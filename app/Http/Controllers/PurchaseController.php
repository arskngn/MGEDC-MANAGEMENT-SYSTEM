<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchasePaymentRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {}

    public function index(Request $request): View
    {
        $perPage  = GeneralSetting::first()?->records_per_page ?? 15;
        $search   = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $status   = $request->input('status');
        $supplierId = $request->input('supplier_id');
        $paymentStatus = $request->input('payment_status');

        $netDueSql = 'purchases.payable_amount - purchases.paid_amount - COALESCE((SELECT SUM(receivable_amount) FROM purchase_returns WHERE purchase_returns.purchase_id = purchases.id AND purchase_returns.deleted_at IS NULL), 0)';

        $purchases = Purchase::query()
            ->with('supplier')
            ->withCount('purchaseReturns')
            ->withSum('purchaseReturns', 'receivable_amount')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($s) use ($search) {
                            $s->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->when(in_array($status, ['ordered', 'received', 'completed']), fn ($q) => $q->where('status', $status))
            ->when($paymentStatus === 'paid', fn ($q) => $q->whereRaw("($netDueSql) <= 0.009"))
            ->when($paymentStatus === 'partial', fn ($q) => $q->whereRaw("($netDueSql) > 0.009 AND purchases.paid_amount > 0.009"))
            ->when($paymentStatus === 'due', fn ($q) => $q->whereRaw("($netDueSql) > 0.009 AND purchases.paid_amount <= 0.009"))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function show(Purchase $purchase): RedirectResponse
    {
        return redirect()->route('purchases.receipt', $purchase);
    }

    public function receipt(Purchase $purchase): View
    {
        $purchase->load(['items.product.unit', 'supplier', 'payments', 'purchaseReturns']);

        // Load batches created from this purchase's items
        $batchesByItem = ProductBatch::whereIn('purchase_item_id', $purchase->items->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('purchase_item_id');

        return view('purchases.receipt', [
            'purchase'       => $purchase,
            'batchesByItem'  => $batchesByItem,
        ]);
    }

    public function receiveItems(Request $request, Purchase $purchase): RedirectResponse
    {
        if (!in_array($purchase->status, ['ordered', 'partial'])) {
            return back()->with('error', 'This purchase has already been fully received.');
        }

        $validated = $request->validate([
            'delivery_date'                  => 'nullable|date',
            'items'                          => 'required|array|min:1',
            'items.*.purchase_item_id'       => 'required|integer|exists:purchase_items,id',
            'items.*.receipt_quantity'       => 'required|numeric|min:0.0001',
            'items.*.batch_number'           => 'nullable|string|max:100',
            'items.*.expiry_date'            => 'nullable|date|after_or_equal:today',
        ]);

        // Filter out zero/empty rows
        $rows = collect($validated['items'])->filter(fn ($i) => (float) $i['receipt_quantity'] > 0);

        if ($rows->isEmpty()) {
            return back()->with('error', 'No items were received. Enter at least one receipt quantity.');
        }

        // Load purchase items to validate totals
        $purchase->load('items');

        // Group rows by purchase_item_id → build batches array per item
        $grouped = [];
        foreach ($rows as $row) {
            $pid = (int) $row['purchase_item_id'];
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'purchase_item_id' => $pid,
                    'receipt_quantity' => 0,
                    'batches' => [],
                ];
            }
            $qty = (float) $row['receipt_quantity'];
            $grouped[$pid]['receipt_quantity'] += $qty;
            $grouped[$pid]['batches'][] = [
                'batch_number' => $row['batch_number'] ?? null,
                'quantity'     => $qty,
                'expiry_date'  => $row['expiry_date'] ?? null,
            ];
        }

        // Validate that total receipt qty per item does not exceed remaining
        foreach ($grouped as $pid => $data) {
            $purchaseItem = $purchase->items->firstWhere('id', $pid);
            if (!$purchaseItem) continue;

            $remaining = max(0, (float) $purchaseItem->quantity - (float) $purchaseItem->received_quantity);

            if ($data['receipt_quantity'] > $remaining + 0.0001) {
                return back()->with('error',
                    "Receipt quantity for \"{$purchaseItem->product_name}\" ({$data['receipt_quantity']}) exceeds remaining quantity ({$remaining})."
                );
            }

            // Validate that batch total exactly equals the submitted receipt_quantity
            $batchTotal = collect($data['batches'])->sum('quantity');
            if (abs($batchTotal - $data['receipt_quantity']) > 0.0001) {
                return back()->with('error',
                    "Batch quantities for \"{$purchaseItem->product_name}\" do not add up to the receipt quantity."
                );
            }

            if (($purchaseItem->product->has_expiry ?? false)) {
                $missingExpiry = collect($data['batches'])->contains(
                    fn (array $batch) => blank($batch['expiry_date'] ?? null)
                );

                if ($missingExpiry) {
                    throw ValidationException::withMessages([
                        'items' => "Expiry date is required for all received batches of \"{$purchaseItem->product_name}\".",
                    ]);
                }
            }

            $pastExpiry = collect($data['batches'])->contains(
                fn (array $batch) => !blank($batch['expiry_date'] ?? null) && \Carbon\Carbon::parse($batch['expiry_date'])->startOfDay()->lt(now()->startOfDay())
            );

            if ($pastExpiry) {
                throw ValidationException::withMessages([
                    'items' => "Expiry date cannot be in the past for batches of \"{$purchaseItem->product_name}\".",
                ]);
            }
        }

        $receiveData = [
            'delivery_date' => $validated['delivery_date'] ?? null,
            'items'         => array_values($grouped),
        ];

        $this->purchaseService->receiveItems($purchase, $receiveData);

        return redirect()->route('purchases.receipt', $purchase)->with('success', 'Items received successfully. Stock has been updated.');
    }


    public function create(): View
    {
        return view('purchases.create', [
            'invoiceNo' => Purchase::nextInvoiceNo(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $totals = $this->purchaseService->calculateTotals(
            $validated['items'],
            (float) $validated['discount'],
            $validated['discount_type'] ?? 'fixed',
            (float) ($validated['tax_rate'] ?? 0)
        );

        if ($totals['payable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        $purchase = $this->purchaseService->createPurchase($validated);

        return redirect()->route('purchases.all')->with('success', 'Purchase placed successfully.');
    }

    public function edit(Purchase $purchase): View
    {
        $purchase->load(['items.product.unit', 'supplier']);

        return view('purchases.edit', [
            'purchase'   => $purchase,
            'suppliers'  => Supplier::orderBy('name')->get(),
            'isLocked'   => ! $purchase->isOrderEditable(),
            'lockReason' => $purchase->orderEditLockReason(),
        ]);
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        if (! $purchase->isOrderEditable()) {
            $message = $purchase->orderEditLockReason() === 'received'
                ? 'You cannot edit a purchase after items have been received. Use purchase return if you need to reverse stock.'
                : 'You cannot edit a purchase after a return has been recorded.';

            return redirect()
                ->route('purchases.edit', $purchase)
                ->with('error', $message);
        }

        $validated = $request->validated();

        $totals = $this->purchaseService->calculateTotals(
            $validated['items'],
            (float) $validated['discount'],
            $validated['discount_type'] ?? 'fixed',
            (float) ($validated['tax_rate'] ?? 0)
        );

        if ($totals['payable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($totals['payable'] < (float) $purchase->paid_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Payable cannot be less than amount already paid.']);
        }

        $this->purchaseService->updatePurchase($purchase, $validated);

        return redirect()->route('purchases.edit', $purchase)->with('success', 'Purchase updated successfully.');
    }

    public function clone(Purchase $purchase): RedirectResponse
    {
        $purchase->load('items');

        $newPurchase = Purchase::create([
            'invoice_no'             => Purchase::nextInvoiceNo(),
            'supplier_id'            => $purchase->supplier_id,
            'purchase_date'          => now()->toDateString(),
            'delivery_date'          => null,
            'note'                   => $purchase->note,
            'subtotal'               => $purchase->subtotal,
            'discount'               => $purchase->discount,
            'discount_type'          => $purchase->discount_type,
            'tax_rate'               => $purchase->tax_rate,
            'tax_amount'             => $purchase->tax_amount,
            'payable_amount'         => $purchase->payable_amount,
            'paid_amount'            => 0,
            'status'                 => 'ordered',
        ]);

        foreach ($purchase->items as $item) {
            $newPurchase->items()->create([
                // Order info — fully cloned
                'product_id'           => $item->product_id,
                'product_name'         => $item->product_name,
                'sku'                  => $item->sku,
                'unit_label'           => $item->unit_label,
                'quantity'             => $item->quantity,
                'unit_price'           => $item->unit_price,
                'unit_discount'        => $item->unit_discount,
                'discount_rate'        => $item->discount_rate,
                'discount_type'        => $item->discount_type,
                'line_total'           => $item->line_total,
                // Receipt-specific fields — reset for fresh order
                'received_quantity'    => 0,
            ]);
        }

        return redirect()->route('purchases.edit', $newPurchase)
            ->with('success', "Cloned from {$purchase->invoice_no}. Review and save.");
    }

    public function storePayment(StorePurchasePaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        $due = (float) $purchase->due_amount;
        $paying = (float) $request->validated()['paying_amount'];
        if ($paying > $due + 0.0001) {
            return back()->withErrors(['paying_amount' => 'Amount cannot exceed the due balance.']);
        }

        $this->purchaseService->recordPayment(
            $purchase,
            $paying,
            $request->user()?->id,
            $request->validated()['payment_method'],
            $request->validated()['check_number'] ?? null,
            $request->validated()['check_date'] ?? null
        );

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->string('q')->trim()->value();
        $products = Product::query()
            ->with('unit')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('sku', 'like', '%' . $q . '%');
                });
            })
            ->when($q === '', function ($query) {
                $query->orderByDesc('id');
            }, function ($query) {
                $query->orderBy('name');
            })
            ->limit(25)
            ->get()
            ->map(function (Product $p) {
                $availability = (float) $p->current_stock;
                
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'unit_label' => $p->unit->short_name ?? $p->unit->name,
                    'availability' => $availability,
                    'fixed_price' => (float) $p->fixed_price,
                    'requires_batch_tracking' => (bool) $p->requires_batch_tracking,
                ];
            });

        return response()->json($products);
    }

    public function exportPdfAll(Request $request)
    {
        $purchases = $this->filteredPurchasesQuery($request)->get();
        $purchases->load(['items.product.unit', 'items.batches', 'supplier', 'purchaseReturns']);
        $generalSetting = GeneralSetting::first();

        // Determine print mode: support ?type=batch or ?type=total for batch/total behavior
        $type = $request->input('type');
        $isBatchInvoicePrint = ($request->has('ids') || $type === 'batch') && $type !== 'total';
        $view = $isBatchInvoicePrint ? 'purchases.pdf.invoices-batch' : 'purchases.pdf.all';
        $orientation = $isBatchInvoicePrint ? 'portrait' : 'landscape';

        $pdfService = app(PdfService::class);
        $data = [
            'purchases' => $purchases,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
            'purchaserName' => auth()->user()?->name ?? 'Admin',
        ];
        $filename = 'purchases-' . time() . '.pdf';

        if ($request->has('print')) {
            return $pdfService->stream($view, $data, $filename, $orientation);
        }

        return $pdfService->download($view, $data, $filename, $orientation);
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $purchases = $this->filteredPurchasesQuery($request)->get();
        $filename = "all-purchases-" . time() . ".csv";

        return response()->streamDownload(function () use ($purchases) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Supplier',
                'Mobile',
                'Total Amount',
                'Discount',
                'Payable',
                'Paid',
                'Due',
            ]);
            foreach ($purchases as $p) {
                fputcsv($out, [
                    $p->invoice_no,
                    $p->purchase_date->format('m/d/Y'),
                    $p->supplier->name,
                    $p->supplier->phone ?? '',
                    formatCurrency($p->subtotal),
                    formatCurrency($p->discount),
                    formatCurrency($p->payable_amount),
                    formatCurrency($p->paid_amount),
                    formatCurrency($p->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdfInvoice(Purchase $purchase, Request $request)
    {
        $purchase->load(['supplier', 'items.product.unit', 'items.batches', 'purchaseReturns']);
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'purchase' => $purchase,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
            'purchaserName' => auth()->user()?->name ?? 'Admin',
        ];
        $filename = "purchase-{$purchase->invoice_no}.pdf";

        if ($request->has('print')) {
            return $pdfService->stream('purchases.pdf.invoice', $data, $filename);
        }

        return $pdfService->download('purchases.pdf.invoice', $data, $filename);
    }

    private function filteredPurchasesQuery(Request $request)
    {
        $search        = $request->string('search')->trim()->value();
        $dateFrom      = $request->input('date_from');
        $dateTo        = $request->input('date_to');
        $status        = $request->input('status');
        $supplierId    = $request->input('supplier_id');
        $paymentStatus = $request->input('payment_status');
        $ids           = $request->input('ids');

        $netDueSql = 'purchases.payable_amount - purchases.paid_amount - COALESCE((SELECT SUM(receivable_amount) FROM purchase_returns WHERE purchase_returns.purchase_id = purchases.id AND purchase_returns.deleted_at IS NULL), 0)';

        return Purchase::query()
            ->with('supplier')
            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($search !== '' && !$ids, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($s) use ($search) {
                            $s->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->when(in_array($status, ['ordered', 'received', 'completed']), fn ($q) => $q->where('status', $status))
            ->when($paymentStatus === 'paid', fn ($q) => $q->whereRaw("($netDueSql) <= 0.009"))
            ->when($paymentStatus === 'partial', fn ($q) => $q->whereRaw("($netDueSql) > 0.009 AND purchases.paid_amount > 0.009"))
            ->when($paymentStatus === 'due', fn ($q) => $q->whereRaw("($netDueSql) > 0.009 AND purchases.paid_amount <= 0.009"))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');
    }


}
