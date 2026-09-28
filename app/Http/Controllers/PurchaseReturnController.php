<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseReturnPaymentRequest;
use App\Http\Requests\StorePurchaseReturnRequest;
use App\Http\Requests\UpdatePurchaseReturnRequest;
use App\Models\GeneralSetting;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseReturnPayment;
use App\Models\Supplier;
use App\Services\PurchaseReturnService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected PurchaseReturnService $purchaseReturnService
    ) {}

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $purchaseId = $request->input('purchase_id');
        $supplierId = $request->input('supplier_id');

        $returns = PurchaseReturn::query()
            ->with(['purchase.supplier', 'purchase.purchaseReturns' => function ($q) {
                $q->orderBy('id', 'asc');
            }])
            ->when($purchaseId, fn ($q) => $q->where('purchase_id', (int) $purchaseId))
            ->when($supplierId, function ($q) use ($supplierId) {
                $q->whereHas('purchase', fn ($p) => $p->where('supplier_id', $supplierId));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('purchase', function ($p) use ($search) {
                            $p->where('invoice_no', 'like', '%'.$search.'%')
                                ->orWhereHas('supplier', function ($s) use ($search) {
                                    $s->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('phone', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('purchase-returns.index', [
            'returns' => $returns,
            'filterPurchaseId' => $purchaseId,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function create(Purchase $purchase): View|RedirectResponse
    {
        $purchase->load(['items.product.unit', 'supplier']);

        if (! $purchase->hasReceivedItems()) {
            return redirect()->route('purchases.all')
                ->with('error', 'Only purchases with received items can be returned.');
        }

        $lines = $purchase->items->map(function (PurchaseItem $line) use ($purchase) {
            $rem = PurchaseReturn::remainingReturnableForPurchaseItem($line);
            $requiresBatchTracking = (bool) $line->product->requires_batch_tracking;
            
            $availableBatches = [];
            if ($requiresBatchTracking) {
                $availableBatches = ProductBatch::where('product_id', $line->product_id)
                     ->where('purchase_id', $purchase->id)
                     ->orderBy('created_at', 'asc')
                     ->get()
                     ->map(function ($batch) {
                         return [
                             'id' => (string) $batch->id,
                             'batch_number' => $batch->batch_number,
                             'quantity_available' => (float) $batch->quantity_available,
                             'expiry_date' => $batch->expiry_date,
                         ];
                     })
                     ->values()
                     ->all();
                     
                if (count($availableBatches) === 0) {
                    $requiresBatchTracking = false;
                }
            }

            return [
                'purchase_item_id' => $line->id,
                'product_name' => $line->product_name,
                'sku' => $line->sku,
                'purchase_quantity' => (float) $line->quantity,
                'stock_quantity' => $rem,
                'return_quantity' => 0,
                'unit_price' => (float) $line->unit_price,
                'unit_label' => $line->unit_label,
                'batches' => $availableBatches,
                'selected_batches' => [],
                'requires_batch_tracking' => $requiresBatchTracking,
            ];
        })->filter(fn (array $l) => $l['stock_quantity'] > 0)->values()->all();

        if ($lines === []) {
            return redirect()->route('purchases.all')
                ->with('error', 'There is no received stock left to return for this purchase.');
        }

        return view('purchase-returns.create', [
            'purchase' => $purchase,
            'lines' => $lines,
            'invoiceNo' => PurchaseReturn::nextInvoiceNo(),
            'currencyCode' => GeneralSetting::first()?->currency ?? 'USD',
        ]);
    }

    public function store(StorePurchaseReturnRequest $request, Purchase $purchase): RedirectResponse
    {
        $purchase->load('items');

        if (! $purchase->hasReceivedItems()) {
            return redirect()->route('purchases.all')
                ->with('error', 'Only purchases with received items can be returned.');
        }

        $discount = round((float) $request->validated()['discount'], 2);
        $totals = $this->purchaseReturnService->calculateTotals($purchase, $request->validated()['items'], $discount);

        if ($totals['lines'] === []) {
            return back()->withInput()->withErrors(['items' => 'Enter a return quantity for at least one product line.']);
        }

        if ($totals['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        $this->purchaseReturnService->createReturn($purchase, $request->validated());

        return redirect()->route('purchases.return')->with('success', 'Purchase return created successfully.');
    }

    public function edit(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['items.purchaseItem', 'items.batches.productBatch', 'purchase.supplier']);
        $purchase = $purchaseReturn->purchase;

        $lines = $purchaseReturn->items->map(function (PurchaseReturnItem $item) use ($purchaseReturn, $purchase) {
            $pi = $item->purchaseItem;
            $rem = PurchaseReturn::remainingReturnableForPurchaseItem($pi, $purchaseReturn->id);
            $requiresBatchTracking = (bool) $pi->product->requires_batch_tracking;
            
            // Collect quantities already deducted by THIS return's batches
            $currentReturnBatchQtys = [];
            foreach ($item->batches as $rb) {
                $currentReturnBatchQtys[$rb->product_batch_id] = 
                    ($currentReturnBatchQtys[$rb->product_batch_id] ?? 0) + (float) $rb->quantity;
            }

            $availableBatches = [];
            if ($requiresBatchTracking) {
                $availableBatches = ProductBatch::where('product_id', $pi->product_id)
                     ->where('purchase_id', $purchase->id)
                     ->orderBy('created_at', 'asc')
                     ->get()
                     ->map(function ($batch) use ($currentReturnBatchQtys) {
                         // Add back the quantity this return already took from this batch
                         $addBack = $currentReturnBatchQtys[$batch->id] ?? 0;
                         return [
                             'id' => (string) $batch->id,
                             'batch_number' => $batch->batch_number,
                             'quantity_available' => (float) $batch->quantity_available + $addBack,
                             'expiry_date' => $batch->expiry_date,
                         ];
                     })
                     ->values()
                     ->all();
                     
                if (count($availableBatches) === 0) {
                    $requiresBatchTracking = false;
                }
            }

            $selectedBatches = $item->batches->map(function ($batch) {
                return [
                    'batch_id' => (string) $batch->product_batch_id,
                    'quantity' => (float) $batch->quantity,
                ];
            })->values()->all();

            return [
                'purchase_item_id' => $item->purchase_item_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'purchase_quantity' => (float) $item->purchase_quantity,
                'stock_quantity' => (float) $item->stock_quantity,
                'return_quantity' => (float) $item->return_quantity,
                'unit_price' => (float) $item->unit_price,
                'unit_label' => $item->unit_label,
                'max_return' => $rem + (float) $item->return_quantity,
                'batches' => $availableBatches,
                'selected_batches' => $selectedBatches,
                'requires_batch_tracking' => $requiresBatchTracking,
            ];
        })->values()->all();

        return view('purchase-returns.edit', [
            'purchaseReturn' => $purchaseReturn,
            'lines' => $lines,
            'currencyCode' => GeneralSetting::first()?->currency ?? 'USD',
        ]);
    }

    public function show(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['items.purchaseItem', 'items.batches.productBatch', 'purchase.supplier']);
        $purchase = $purchaseReturn->purchase;

        $lines = $purchaseReturn->items->map(function ($item) {
            $selectedBatches = $item->batches->map(function ($b) {
                return [
                    'batch_number' => optional($b->productBatch)->batch_number ?? 'N/A',
                    'quantity' => (float) $b->quantity,
                ];
            })->values()->all();

            return [
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'purchase_quantity' => (float) $item->purchase_quantity,
                'return_quantity' => (float) $item->return_quantity,
                'unit_label' => $item->unit_label,
                'batches' => $selectedBatches,
            ];
        })->values()->all();

        return view('purchase-returns.show', [
            'purchaseReturn' => $purchaseReturn,
            'lines' => $lines,
            'currencyCode' => GeneralSetting::first()?->currency ?? 'USD',
        ]);
    }

    public function update(UpdatePurchaseReturnRequest $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $purchase = $purchaseReturn->purchase;
        $discount = round((float) $request->validated()['discount'], 2);
        $totals = $this->purchaseReturnService->calculateTotals($purchase, $request->validated()['items'], $discount, $purchaseReturn->id);

        if ($totals['lines'] === []) {
            return back()->withInput()->withErrors(['items' => 'Enter a return quantity for at least one product line.']);
        }

        if ($totals['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($totals['receivable'] < (float) $purchaseReturn->received_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Receivable cannot be less than amount already received.']);
        }

        $this->purchaseReturnService->updateReturn($purchaseReturn, $request->validated());

        return redirect()->route('purchases.return')->with('success', 'Purchase return updated successfully.');
    }

    public function storePayment(StorePurchaseReturnPaymentRequest $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        // Calculate effective due using progressive ledger offset (same as view)
        $purchase = $purchaseReturn->purchase;
        $remainingUnpaid = max(0, (float)$purchase->payable_amount - (float)$purchase->paid_amount);
        $effectiveDue = 0;

        foreach ($purchase->purchaseReturns()->orderBy('id', 'asc')->get() as $pr) {
            $receivable = (float)$pr->receivable_amount;
            $offset = min($receivable, $remainingUnpaid);
            $cashOwed = $receivable - $offset;
            $remainingUnpaid -= $offset;

            if ($pr->id === $purchaseReturn->id) {
                $effectiveDue = max(0, $cashOwed - (float)$pr->received_amount);
                break;
            }
        }

        $recv = (float) $request->validated()['receiving_amount'];
        if ($recv > $effectiveDue + 0.0001) {
            return back()->withErrors(['receiving_amount' => 'Amount cannot exceed the effective due balance.']);
        }

        $this->purchaseReturnService->recordPayment(
            $purchaseReturn, 
            $recv, 
            $request->user()?->id,
            $request->validated()['payment_method'],
            $request->validated()['check_number'] ?? null,
            $request->validated()['check_date'] ?? null
        );

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $returns = $this->filteredReturnsQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        // Support batch vs total consolidated print/download
        $type = $request->input('type');
        $isBatchPrint = ($request->has('ids') || $type === 'batch') && $type !== 'total';
        $view = $isBatchPrint ? 'purchase-returns.pdf.invoices-batch' : 'purchase-returns.pdf.all';
        $orientation = $isBatchPrint ? 'portrait' : 'landscape';

        $pdfService = app(PdfService::class);
        $data = [
            'returns' => $returns,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
            'purchaserName' => auth()->user()?->name ?? 'Admin',
        ];
        $filename = 'all-purchase-returns-'.time().'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream($view, $data, $filename, $orientation);
        }

        return $pdfService->download($view, $data, $filename, $orientation);
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $returns = $this->filteredReturnsQuery($request)->get();
        $filename = 'all-purchase-returns-'.time().'.csv';

        return response()->streamDownload(function () use ($returns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Supplier',
                'Mobile',
                'Total Amount',
                'Discount',
                'Receivable',
                'Received',
                'Due',
            ]);
            foreach ($returns as $r) {
                fputcsv($out, [
                    $r->return_invoice_no,
                    $r->return_date->format('m/d/Y'),
                    $r->purchase->supplier->name,
                    $r->purchase->supplier->phone ?? '',
                    formatCurrency($r->subtotal),
                    formatCurrency($r->discount),
                    formatCurrency($r->receivable_amount),
                    formatCurrency($r->received_amount),
                    formatCurrency($r->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdfInvoice(PurchaseReturn $purchaseReturn, Request $request)
    {
        $purchaseReturn->load(['items', 'items.batches.productBatch', 'purchase.supplier']);
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'purchaseReturn' => $purchaseReturn,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
            'purchaserName' => auth()->user()?->name ?? 'Admin',
        ];
        $filename = 'purchase-return-'.$purchaseReturn->return_invoice_no.'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('purchase-returns.pdf.invoice', $data, $filename);
        }

        return $pdfService->download('purchase-returns.pdf.invoice', $data, $filename);
    }

    private function filteredReturnsQuery(Request $request)
    {
        $search     = $request->string('search')->trim()->value();
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        $purchaseId = $request->input('purchase_id');
        $supplierId = $request->input('supplier_id');
        $ids        = $request->input('ids');

        return PurchaseReturn::query()
            ->with('purchase.supplier')
            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($purchaseId && !$ids, fn ($q) => $q->where('purchase_id', (int) $purchaseId))
            ->when($supplierId && !$ids, function ($q) use ($supplierId) {
                $q->whereHas('purchase', fn ($p) => $p->where('supplier_id', $supplierId));
            })
            ->when($search !== '' && !$ids, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('purchase', function ($p) use ($search) {
                            $p->where('invoice_no', 'like', '%'.$search.'%')
                                ->orWhereHas('supplier', function ($s) use ($search) {
                                    $s->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('phone', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
            ->orderByDesc('id');
    }


}
