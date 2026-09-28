<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalePaymentRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\StoreSaleReturnRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnPayment;
use App\Models\User;
use App\Actions\Sales\CreateSaleAction;
use App\Services\BatchManagementService;
use App\Services\SaleReturnService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function __construct(
        protected SaleReturnService $saleReturnService,
        protected BatchManagementService $batchService,
    ) {}

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $sales = $this->filteredSalesQuery($request)
            ->withCount(['saleReturns' => fn ($q) => $q->where('status', '!=', SaleReturn::STATUS_REJECTED)])
            ->withSum(['saleReturns' => fn ($q) => $q->where('status', '!=', SaleReturn::STATUS_REJECTED)], 'payable_amount')
            ->paginate($perPage)
            ->withQueryString();

        return view('sales.index', [
            'sales'        => $sales,
            'salespersons' => Cache::remember('salespersons_list', 3600, fn () => User::ofRole('salesperson')->get()),
            'routes'       => \App\Models\CustomerRoute::active()->orderBy('name')->get(),
        ]);

    }

    public function create(): View
    {
        return view('sales.create', [
            'invoiceNo' => Sale::nextInvoiceNo(),
            'customers' => Customer::active()->with('route')->orderByRaw('name')->get()->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'address' => $c->address,
                'route' => $c->route?->display_name,
                'store_category' => $c->storeCategory?->name,
            ]),
            'salespersons' => User::ofRole('salesperson')->get(),
        ]);
    }

    public function show(Sale $sale): View
    {
        $sale->load(['items.product.unit', 'items.batches.productBatch', 'customer', 'payments', 'salesperson', 'saleReturns.items', 'saleReturns.payments']);
        
        return view('sales.show', [
            'sale' => $sale,
        ]);
    }

    public function store(StoreSaleRequest $request, CreateSaleAction $action, \App\Actions\Sales\CalculateSaleTotalsAction $calculateTotals): RedirectResponse
    {
        $validated = $request->validated();
        $totals = $calculateTotals->execute($validated['items'], (float) $validated['discount'], $validated['discount_type'] ?? 'fixed');
        
        if ($totals['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        $sale = $action->execute($validated);

        if (Auth::user()->hasRole('salesperson')) {
            // Notify admins and encoders
            $notifiableUsers = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['admin', 'encoder']);
            })->get();

            foreach ($notifiableUsers as $notifiableUser) {
                \App\Models\SystemNotification::create([
                    'user_id' => $notifiableUser->id,
                    'type' => 'sale_approval',
                    'title' => 'Sale Approval Required',
                    'message' => 'Salesperson ' . Auth::user()->name . ' created sale ' . $sale->invoice_no . ' and it requires confirmation.',
                    'severity' => 'warning',
                    'meta' => [
                        'url' => route('sales.all', ['search' => $sale->invoice_no])
                    ],
                ]);
            }
            return redirect()->route('sales.all')->with('success', 'Sale submitted for approval. An encoder or admin will confirm it.');
        }

        return redirect()->route('sales.all')->with('success', 'Sale created successfully.');
    }

    public function cancel(Sale $sale): RedirectResponse
    {
        if (! Auth::user()?->hasPermission('Cancel Sale')) {
            abort(403);
        }

        if ($sale->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed sales can be cancelled.');
        }

        if ($sale->delivery_status !== Sale::DELIVERY_NOT_DISPATCHED) {
            return back()->with('error', 'Only orders that have not been dispatched yet can be cancelled.');
        }

        if ($sale->paid_amount > 0) {
            return back()->with('error', 'Cannot cancel a sale that has already received payment. Please process a return instead.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load(['items.batches.productBatch']);

            foreach ($sale->items as $item) {
                foreach ($item->batches as $saleBatch) {
                    $productBatch = $saleBatch->productBatch;
                    if ($productBatch) {
                        $this->batchService->returnStockToBatch(
                            $productBatch,
                            (float) $saleBatch->quantity,
                            $sale
                        );
                    }
                }
            }

            $sale->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Sale cancelled and stock restored successfully.');
    }

    public function confirm(Sale $sale): RedirectResponse
    {
        if (! Auth::user()?->hasPermission('Confirm Sale')) {
            abort(403);
        }
        $sale->update(['status' => 'confirmed']);
        return back()->with('success', 'Sale confirmed successfully.');
    }

    public function reject(Sale $sale): RedirectResponse
    {
        if (! Auth::user()?->hasPermission('Reject Sale')) {
            abort(403);
        }

        DB::transaction(function () use ($sale) {
            $sale->load(['items.batches.productBatch']);

            foreach ($sale->items as $item) {
                foreach ($item->batches as $saleBatch) {
                    $productBatch = $saleBatch->productBatch;
                    if ($productBatch) {
                        $this->batchService->returnStockToBatch(
                            $productBatch,
                            (float) $saleBatch->quantity,
                            $sale
                        );
                    }
                }
            }

            $sale->update(['status' => 'rejected']);
        });

        return back()->with('success', 'Sale rejected and stock restored.');
    }

    public function updateDeliveryStatus(Request $request, Sale $sale): RedirectResponse
    {
        if (! Auth::user()?->hasPermission('Update Sale Delivery Status')) {
            abort(403, 'You are not authorized to update the delivery status.');
        }

        if ($sale->status !== 'confirmed') {
            return back()->with('error', 'Delivery status can only be updated on confirmed sales.');
        }

        $validated = $request->validate([
            'delivery_status' => ['required', 'in:' . implode(',', array_keys(Sale::$deliveryStatuses))],
            'delivery_note'   => ['nullable', 'string', 'max:500'],
        ]);

        $payload = [
            'delivery_status' => $validated['delivery_status'],
            'delivery_note'   => $validated['delivery_note'] ?? $sale->delivery_note,
        ];

        if ($validated['delivery_status'] === Sale::DELIVERY_DELIVERED) {
            $payload['delivered_at'] = now();
        }

        $sale->update($payload);

        // Log the activity
        \App\Models\ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => 'updated_delivery_status',
            'model_type' => get_class($sale),
            'model_id'   => $sale->id,
            'changes'    => [
                'delivery_status' => $validated['delivery_status'],
                'delivery_note'   => $validated['delivery_note'] ?? null,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notify admins/encoders on key delivery milestones
        if (in_array($validated['delivery_status'], [Sale::DELIVERY_DELIVERED, Sale::DELIVERY_FAILED, Sale::DELIVERY_RETURNED])) {
            $notifiableUsers = \App\Models\User::whereHas('role', fn ($q) =>
                $q->whereIn('name', ['admin', 'encoder'])
            )->get();

            $severity = $validated['delivery_status'] === Sale::DELIVERY_DELIVERED ? 'success' : 'warning';
            $message  = 'Delivery for sale ' . $sale->invoice_no
                . ' is now: ' . Sale::$deliveryStatuses[$validated['delivery_status']]
                . '. Updated by ' . Auth::user()->name . '.';

            foreach ($notifiableUsers as $notifiableUser) {
                \App\Models\SystemNotification::create([
                    'user_id'  => $notifiableUser->id,
                    'type'     => 'delivery_update',
                    'title'    => 'Delivery Status Update',
                    'message'  => $message,
                    'severity' => $severity,
                    'meta'     => ['url' => route('sales.show', $sale)],
                ]);
            }
        }

        return redirect()->route('sales.show', $sale)->with('success', 'Delivery status updated to "' . Sale::$deliveryStatuses[$validated['delivery_status']] . '".');
    }

    public function bulkUpdateDeliveryStatus(Request $request)
    {
        if (! Auth::user()?->hasPermission('Update Sale Delivery Status')) {
            abort(403, 'You are not authorized to update the delivery status.');
        }

        // Parse comma-separated IDs sent from the Alpine hidden input
        $rawIds  = $request->input('sale_ids', '');
        $saleIds = array_values(array_filter(array_map('intval', explode(',', $rawIds))));

        if (empty($saleIds)) {
            $msg = 'No sale IDs provided.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $request->validate([
            'delivery_status' => ['required', 'in:' . implode(',', array_keys(Sale::$deliveryStatuses))],
            'delivery_note'   => ['nullable', 'string', 'max:500'],
        ]);

        $deliveryStatus = $request->input('delivery_status');
        $deliveryNote   = $request->input('delivery_note');

        $sales = Sale::whereIn('id', $saleIds)
            ->where('status', 'confirmed')
            ->get();

        if ($sales->isEmpty()) {
            $msg = 'No confirmed sales were found for the selected IDs.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $payload = [
            'delivery_status' => $deliveryStatus,
            'delivery_note'   => $deliveryNote,
        ];

        if ($deliveryStatus === Sale::DELIVERY_DELIVERED) {
            $payload['delivered_at'] = now();
        }

        $notifiable      = in_array($deliveryStatus, [Sale::DELIVERY_DELIVERED, Sale::DELIVERY_FAILED, Sale::DELIVERY_RETURNED]);
        $notifiableUsers = $notifiable
            ? \App\Models\User::whereHas('role', fn ($q) => $q->whereIn('name', ['admin', 'encoder']))->get()
            : collect();

        foreach ($sales as $sale) {
            $sale->update($payload);

            \App\Models\ActivityLog::create([
                'user_id'    => Auth::id(),
                'action'     => 'bulk_updated_delivery_status',
                'model_type' => get_class($sale),
                'model_id'   => $sale->id,
                'changes'    => [
                    'delivery_status' => $deliveryStatus,
                    'delivery_note'   => $deliveryNote,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($notifiable && $notifiableUsers->isNotEmpty()) {
                $severity = $deliveryStatus === Sale::DELIVERY_DELIVERED ? 'success' : 'warning';
                $message  = 'Delivery for sale ' . $sale->invoice_no
                    . ' is now: ' . Sale::$deliveryStatuses[$deliveryStatus]
                    . '. Updated by ' . Auth::user()->name . '.';

                foreach ($notifiableUsers as $notifiableUser) {
                    \App\Models\SystemNotification::create([
                        'user_id'  => $notifiableUser->id,
                        'type'     => 'delivery_update',
                        'title'    => 'Delivery Status Update',
                        'message'  => $message,
                        'severity' => $severity,
                        'meta'     => ['url' => route('sales.show', $sale)],
                    ]);
                }
            }
        }

        $label = Sale::$deliveryStatuses[$deliveryStatus];
        $msg   = $sales->count() . ' sale(s) delivery status updated to "' . $label . '".';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function edit(Sale $sale): View
    {
        $user = Auth::user();

        // Allow if user has the general Edit Sale permission
        // OR if the user is a salesperson who owns this pending sale
        $isSalespersonOwner = $user->hasRole('salesperson')
            && (int) $sale->salesperson_id === (int) $user->id
            && $sale->status === 'pending';

        if (!$isSalespersonOwner && !$user->hasPermission('Edit Sale')) {
            abort(403, 'You are not authorized to edit this sale.');
        }

        $sale->load(['items.product.unit', 'items.batches.productBatch', 'customer.storeCategory', 'storeCategory']);

        $customers = Customer::query()
            ->where(function ($q) use ($sale) {
                $q->active()
                    ->orWhere('id', $sale->customer_id);
            })
            ->orderByRaw('name')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'address' => $c->address,
                'store_category' => $c->storeCategory?->name,
            ]);

        // Prepare initial items data
        $initialItems = $sale->items->map(function ($item) use ($sale) {
            $onSaleByBatch = $item->batches->mapWithKeys(fn ($sb) => [
                $sb->product_batch_id => (float) $sb->quantity,
            ]);
            $saleBatchIds = $item->batches->pluck('product_batch_id');

            // Load batches for this product across all locations (include batches used in this sale)
            $productBatches = ProductBatch::query()
                ->where('product_id', $item->product_id)
                ->where('status', ProductBatch::STATUS_ACTIVE)
                ->where(function ($query) use ($saleBatchIds) {
                    $query->where('quantity_available', '>', 0);
                    if ($saleBatchIds->isNotEmpty()) {
                        $query->orWhereIn('id', $saleBatchIds);
                    }
                })
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'quantity_available' => (float) $b->quantity_available + (float) ($onSaleByBatch[$b->id] ?? 0),
                    'expiry_date' => null,
                ]);

            // Prepare selected batches: only load if use_fefo is false!
            $useFefo = (bool) $item->use_fefo;
            if ($useFefo) {
                $selectedBatches = [];
            } else {
                $selectedBatches = $item->batches->map(fn($ib) => [
                    'batch_id' => $ib->product_batch_id,
                    'quantity' => (float) $ib->quantity,
                ])->values()->all();
            }

            return [
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->sku,
                'unit_label' => $item->unit_label,
                'in_stock' => $productBatches->sum('quantity_available'),
                'quantity' => (float) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'unit_discount' => (float) ($item->unit_discount ?? 0),
                'unit_discount_type' => $item->unit_discount_type ?? 'fixed',
                'discount_rate' => (float) ($item->discount_rate ?? 0),
                'batches' => $productBatches,
                'selected_batches' => $selectedBatches,
                'use_fefo' => $useFefo,
                'requires_batch_tracking' => (bool) $item->product->requires_batch_tracking,
                'is_free' => (bool) $item->is_free,
            ];
        })->values()->all();

        return view('sales.edit', [
            'sale' => $sale,
            'customers' => $customers,
            'hasReturns' => $sale->hasReturns(),
            'initialItems' => $initialItems,
            'salespersons' => User::ofRole('salesperson')->get(),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale, \App\Actions\Sales\UpdateSaleAction $action, \App\Actions\Sales\CalculateSaleTotalsAction $calculateTotals): RedirectResponse
    {
        $user = Auth::user();

        // Allow if user has the general Update Sale permission
        // OR if the user is a salesperson who owns this pending sale
        $isSalespersonOwner = $user->hasRole('salesperson')
            && (int) $sale->salesperson_id === (int) $user->id
            && $sale->status === 'pending';

        if (!$isSalespersonOwner && !$user->hasPermission('Update Sale')) {
            abort(403, 'You are not authorized to update this sale.');
        }

        if ($sale->hasReturns()) {
            return back()->with('error', 'You cannot edit a sale after a return has been recorded.');
        }

        $validated = $request->validated();
        $totals = $calculateTotals->execute($validated['items'], (float) $validated['discount'], $validated['discount_type'] ?? 'fixed');
        
        if ($totals['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($totals['receivable'] < (float) $sale->paid_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Receivable cannot be less than amount already received.']);
        }

        $action->execute($sale, $validated);

        return redirect()->route('sales.all')->with('success', 'Sale updated successfully.');
    }

    public function storePayment(StoreSalePaymentRequest $request, Sale $sale, \App\Actions\Sales\ReceiveSalePaymentAction $action): RedirectResponse
    {
        $due = $sale->due_amount;
        $receiving = (float) $request->validated()['receiving_amount'];
        if ($receiving > $due + 0.0001) {
            return back()->withErrors(['receiving_amount' => 'Amount cannot exceed the due balance.']);
        }

        $action->execute(
            $sale,
            $receiving,
            $request->user()?->id,
            $request->validated()['payment_method'],
            $request->validated()['check_number'] ?? null,
            $request->validated()['check_date'] ?? null
        );

        return back()->with('success', 'Payment received successfully.');
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
                $query->orderByRaw('id DESC');
            }, function ($query) {
                $query->orderByRaw('name');
            })
            ->limit(25)
            ->get()
            ->map(function (Product $p) {
                $inStock = $p->current_stock;

                // Load available batches for batch-tracked products so the FEFO/batch UI works
                if ($p->requires_batch_tracking) {
                    $batches = \App\Models\ProductBatch::where('product_id', $p->id)
                        ->where('status', 'active')
                        ->where('quantity_available', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->get()
                        ->map(fn($b) => [
                            'id' => $b->id,
                            'batch_number' => $b->batch_number,
                            'quantity_available' => (float) $b->quantity_available,
                            'expiry_date' => null,
                        ])->values()->all();
                } else {
                    $batches = [];
                }

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'unit_label' => $p->unit->short_name ?? $p->unit->name,
                    'in_stock' => $inStock,
                    'fixed_price' => (float) $p->fixed_price,
                    'batches' => $batches,
                    'requires_batch_tracking' => (bool) $p->requires_batch_tracking,
                ];
            });

        return response()->json($products);
    }

    public function returnCreate(Sale $sale): View|RedirectResponse
    {
        if ($sale->status !== 'confirmed') {
            return redirect()->route('sales.show', $sale)->with('error', 'Sale returns can only be created for confirmed sales.');
        }

        $sale->load(['customer', 'items.product.unit', 'items.batches.productBatch']);

        $lines = $sale->items->map(function (SaleItem $line) {
            // Prepare batches for this sale item
            $batches = $line->batches->map(function ($batch) use ($line) {
                $remaining = SaleReturn::remainingReturnableForSaleItemBatch($batch);
                return [
                    'batch_id' => $batch->product_batch_id,
                    'batch_number' => optional($batch->productBatch)->batch_number ?? 'N/A',
                    'expiry_date' => null,
                    'sale_quantity' => (float) $batch->quantity,
                    'returnable_quantity' => $remaining,
                    'good_quantity' => 0,
                    'damaged_quantity' => 0,
                ];
            })->values()->all();
            
            // Calculate rem: sum of batch returnables if batches exist, else use original method
            if (!empty($batches)) {
                $rem = collect($batches)->sum('returnable_quantity');
            } else {
                $rem = SaleReturn::remainingReturnableForSaleItem($line);
            }

            return [
                'sale_item_id' => $line->id,
                'product_name' => $line->product_name,
                'sku' => $line->sku,
                'sale_quantity' => (float) $line->quantity,
                'returnable_quantity' => $rem,
                'good_quantity' => 0,
                'damaged_quantity' => 0,
                'unit_price' => (float) $line->unit_price,
                'unit_label' => $line->unit_label,
                'batches' => $batches,
                'use_fefo' => (bool) $line->use_fefo,
                'requires_batch_tracking' => (bool) ((optional($line->product)->requires_batch_tracking ?? false) || ! empty($batches)),
            ];
        })->filter(fn (array $l) => $l['returnable_quantity'] > 0)->values()->all();

        if ($lines === []) {
            return redirect()->route('sales.all')
                ->with('error', 'There is no sold stock left to return for this sale.');
        }

        return view('sales.return-create', [
            'sale' => $sale,
            'lines' => $lines,
            'returnInvoiceNo' => SaleReturn::nextReturnNo(),
        ]);
    }

    public function returnStore(StoreSaleReturnRequest $request, Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'confirmed') {
            return redirect()->route('sales.show', $sale)->with('error', 'Sale returns can only be created for confirmed sales.');
        }

        $sale->load(['items.batches', 'customer']);

        try {
            $saleReturn = $this->saleReturnService->createReturn($sale, $request->validated());

            if (Auth::user()?->hasRole('salesperson')) {
                // Notify admins and encoders
                $notifiableUsers = User::whereHas('role', function ($q) {
                    $q->whereIn('name', ['admin', 'encoder']);
                })->get();

                foreach ($notifiableUsers as $notifiableUser) {
                    \App\Models\SystemNotification::create([
                        'user_id' => $notifiableUser->id,
                        'type' => 'sale_return_approval',
                        'title' => 'Sale Return Approval Required',
                        'message' => 'Salesperson ' . Auth::user()->name . ' created return ' . $saleReturn->return_invoice_no . ' for sale ' . $sale->invoice_no . ' and it requires confirmation.',
                        'severity' => 'warning',
                        'meta' => [
                            'url' => route('sales.return', ['search' => $saleReturn->return_invoice_no])
                        ],
                    ]);
                }
                return redirect()->route('sales.return')->with('success', 'Sale return submitted for approval. An encoder or admin will confirm it.');
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()->route('sales.return')->with('success', 'Sale return recorded successfully.');
    }

    public function confirmReturn(SaleReturn $saleReturn): RedirectResponse
    {
        if (! Auth::user()?->hasPermission('Confirm Sale Return')) {
            abort(403);
        }
        $saleReturn->update(['status' => 'confirmed']);
        return back()->with('success', 'Sale return confirmed successfully.');
    }

    public function rejectReturn(SaleReturn $saleReturn): RedirectResponse
    {
        if (! Auth::user()?->hasPermission('Reject Sale Return')) {
            abort(403);
        }

        $this->saleReturnService->rejectReturn($saleReturn);

        return back()->with('success', 'Sale return rejected and stock reversed.');
    }

    public function returnEdit(SaleReturn $saleReturn): View|RedirectResponse
    {
        if ($saleReturn->status !== 'confirmed') {
            return redirect()->route('sales.return')->with('error', 'Sale returns can only be edited once confirmed.');
        }

        $saleReturn->load(['items.batches.productBatch', 'sale.customer', 'sale.items.product.unit', 'sale.items.batches.productBatch']);
        $sale = $saleReturn->sale;

        $lines = $sale->items->map(function (SaleItem $line) use ($saleReturn) {
            $existingReturnItem = $saleReturn->items->firstWhere('sale_item_id', $line->id);
            
            // Prepare batches for this sale item
            $batches = $line->batches->map(function ($batch) use ($saleReturn, $existingReturnItem) {
                $remaining = SaleReturn::remainingReturnableForSaleItemBatch($batch, $saleReturn->id);

                $existingBatches = $existingReturnItem
                    ? $existingReturnItem->batches->where('product_batch_id', $batch->product_batch_id)
                    : collect();

                return [
                    'batch_id' => $batch->product_batch_id,
                    'batch_number' => optional($batch->productBatch)->batch_number ?? 'N/A',
                    'expiry_date' => null,
                    'sale_quantity' => (float) $batch->quantity,
                    'returnable_quantity' => $remaining,
                    'good_quantity' => (float) $existingBatches->where('condition', 'good')->sum('quantity'),
                    'damaged_quantity' => (float) $existingBatches->where('condition', 'damaged')->sum('quantity'),
                ];
            })->values()->all();
            
            // Calculate rem: sum of batch returnables if batches exist, else use original method
            if (!empty($batches)) {
                $rem = collect($batches)->sum('returnable_quantity');
            } else {
                $rem = SaleReturn::remainingReturnableForSaleItem($line, $saleReturn->id);
            }

            return [
                'sale_item_id' => $line->id,
                'product_name' => $line->product_name,
                'sku' => $line->sku,
                'sale_quantity' => (float) $line->quantity,
                'returnable_quantity' => $rem,
                'good_quantity' => (float) ($existingReturnItem?->batches->where('condition', 'good')->sum('quantity') ?? 0),
                'damaged_quantity' => (float) ($existingReturnItem?->batches->where('condition', 'damaged')->sum('quantity') ?? 0),
                'unit_price' => (float) $line->unit_price,
                'unit_label' => $line->unit_label,
                'batches' => $batches,
                'use_fefo' => (bool) $line->use_fefo,
                'requires_batch_tracking' => (bool) (optional($line->product)->requires_batch_tracking ?? false || ! empty($batches)),
            ];
        })->values()->all();

        return view('sales.return-edit', [
            'sale' => $sale,
            'saleReturn' => $saleReturn,
            'lines' => $lines,
            'returnInvoiceNo' => $saleReturn->return_invoice_no,
        ]);
    }

    public function returnShow(SaleReturn $saleReturn): View
    {
        $saleReturn->load(['items.batches.productBatch', 'sale.customer', 'sale.items.product.unit']);
        $sale = $saleReturn->sale;

        // Prepare lines for read-only display
        $lines = $saleReturn->items->map(function ($ri) use ($sale) {
            $saleItem = $sale->items->firstWhere('id', $ri->sale_item_id);
            $batches = $ri->batches->map(function ($b) {
                return [
                    'batch_number' => optional($b->productBatch)->batch_number ?? 'N/A',
                    'quantity' => (float) $b->quantity,
                    'condition' => $b->condition,
                ];
            })->values()->all();

            $goodQty = (float) $ri->batches->where('condition', 'good')->sum('quantity');
            $damagedQty = (float) $ri->batches->where('condition', 'damaged')->sum('quantity');
            if ($ri->batches->isEmpty()) {
                $goodQty = (float) $ri->return_quantity;
            }

            return [
                'product_name' => $ri->product_name,
                'sku' => $ri->sku,
                'sale_quantity' => $saleItem ? (float) $saleItem->quantity : 0,
                'good_quantity' => $goodQty,
                'damaged_quantity' => $damagedQty,
                'unit_label' => $ri->unit_label,
                'batches' => $batches,
            ];
        })->values()->all();

        return view('sales.return-show', [
            'sale' => $sale,
            'saleReturn' => $saleReturn,
            'lines' => $lines,
        ]);
    }

    public function returnUpdate(StoreSaleReturnRequest $request, SaleReturn $saleReturn): RedirectResponse
    {
        if ($saleReturn->status !== 'confirmed') {
            return redirect()->route('sales.return')->with('error', 'Sale returns can only be edited once confirmed.');
        }

        $saleReturn->load(['sale.items.batches', 'sale.customer']);

        try {
            $this->saleReturnService->updateReturn($saleReturn, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        return redirect()->route('sales.return')->with('success', 'Sale return updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $type = $request->input('type');
        $generalSetting = GeneralSetting::first();

        if ($type === 'loading_list' || $type === 'setlist') {
            $sales = $this->filteredSalesQuery($request)
                ->with([
                    'items' => function ($query) {
                        $query->select(
                            'id',
                            'sale_id',
                            'product_id',
                            'sku',
                            'product_name',
                            'unit_label',
                            'quantity',
                            'is_free'
                        );
                    },
                    'saleReturns.items' => function ($query) {
                        $query->select(
                            'id',
                            'sale_return_id',
                            'sale_item_id',
                            'product_id',
                            'return_quantity'
                        );
                    }
                ])
                ->get();

            $consolidated = [];
            foreach ($sales as $sale) {
                // Map return quantities per sale_item_id
                $returnedMap = [];
                foreach ($sale->saleReturns as $saleReturn) {
                    foreach ($saleReturn->items as $rItem) {
                        $sItemId = $rItem->sale_item_id;
                        $returnedMap[$sItemId] = ($returnedMap[$sItemId] ?? 0.0) + (float) $rItem->return_quantity;
                    }
                }

                foreach ($sale->items as $item) {
                    $pid = $item->product_id;
                    $returnedQty = $returnedMap[$item->id] ?? 0.0;
                    $netQty = max(0.0, (float) $item->quantity - $returnedQty);

                    if (! isset($consolidated[$pid])) {
                        $consolidated[$pid] = [
                            'sku' => $item->sku,
                            'product_name' => $item->product_name,
                            'unit_label' => $item->unit_label,
                            'paid_qty' => 0.0,
                            'free_qty' => 0.0,
                            'returned_qty' => 0.0,
                            'total_qty' => 0.0,
                        ];
                    }

                    if ($item->is_free) {
                        $consolidated[$pid]['free_qty'] += $netQty;
                    } else {
                        $consolidated[$pid]['paid_qty'] += $netQty;
                    }
                    $consolidated[$pid]['returned_qty'] += $returnedQty;
                    $consolidated[$pid]['total_qty'] += $netQty;
                }
            }

            // Filter out items where net total_qty to load is 0
            $consolidated = array_filter($consolidated, fn($i) => $i['total_qty'] > 0.0001);

            usort($consolidated, fn ($a, $b) => strcmp($a['product_name'], $b['product_name']));

            $pdfService = app(PdfService::class);
            $data = [
                'items' => $consolidated,
                'salesCount' => $sales->count(),
                'invoiceNos' => $sales->pluck('invoice_no')->implode(', '),
                'generalSetting' => $generalSetting,
                'note' => trim((string) $request->input('note', '')),
            ];
            $filename = 'loading-list-'.time().'.pdf';

            if ($request->has('print')) {
                return $pdfService->stream('sales.pdf.loading-list', $data, $filename, 'portrait');
            }

            return $pdfService->download('sales.pdf.loading-list', $data, $filename, 'portrait');
        }

        $sales = $this->filteredSalesQuery($request)->get();
        $sales->load(['items.product.unit', 'customer.storeCategory', 'storeCategory', 'salesperson', 'user', 'saleReturns']);

        $storeCategories = $sales->map(fn ($sale) =>
            $sale->storeCategory?->name
            ?? $sale->store_category
            ?? $sale->customer?->storeCategory?->name
            ?? $sale->customer?->store_category
        )->filter()->unique();
        $commonStoreCategory = $storeCategories->count() === 1 ? $storeCategories->first() : null;

        $isBatchInvoicePrint = ($request->has('ids') || $type === 'batch') && $type !== 'total';
        $view = $isBatchInvoicePrint ? 'sales.pdf.invoices-batch' : 'sales.pdf.all';
        $orientation = $isBatchInvoicePrint ? 'portrait' : 'landscape';

        $pdfService = app(PdfService::class);
        $data = [
            'sales' => $sales,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
            'commonStoreCategory' => $commonStoreCategory,
        ];
        $filename = 'sales-'.time().'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream($view, $data, $filename, $orientation);
        }

        return $pdfService->download($view, $data, $filename, $orientation);
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $sales = $this->filteredSalesQuery($request)->get();
        $filename = 'all-sales-'.time().'.csv';

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Customer',
                'Mobile',
                'Total Amount',
                'Discount',
                'Receivable',
                'Received',
                'Due',
            ]);
            foreach ($sales as $s) {
                fputcsv($out, [
                    $s->invoice_no,
                    $s->sale_date->format('m/d/Y'),
                    $s->customer->name,
                    $s->customer->phone ?? '',
                    formatCurrency($s->subtotal),
                    formatCurrency($s->discount),
                    formatCurrency($s->receivable_amount),
                    formatCurrency($s->paid_amount),
                    formatCurrency($s->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdfInvoice(Sale $sale, Request $request)
    {
        if ($sale->status !== 'confirmed') {
            return redirect()->route('sales.show', $sale)->with('error', 'Invoices can only be printed or downloaded for confirmed sales.');
        }

        $sale->load(['customer', 'items.product.unit']);
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'sale' => $sale,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'sale-'.$sale->invoice_no.'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('sales.pdf.invoice', $data, $filename);
        }

        return $pdfService->download('sales.pdf.invoice', $data, $filename);
    }

    private function filteredSalesQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $ids = $request->input('ids');
        $salespersonId = $request->input('salesperson_id');
        $paymentStatus = $request->input('payment_status');
        $deliveryStatus = $request->input('delivery_status');
        $routeId = $request->input('route_id');

        $netDueSql = 'sales.receivable_amount - sales.paid_amount - COALESCE((SELECT SUM(payable_amount) FROM sale_returns WHERE sale_returns.sale_id = sales.id AND sale_returns.deleted_at IS NULL), 0)';

        return Sale::query()
            ->with(['customer', 'salesperson', 'items'])
            ->leftJoin('users', 'sales.salesperson_id', '=', 'users.id')
            ->select('sales.*')
            ->when(auth()->user()->hasRole('salesperson') || (auth()->user()->hasPermission('View Own Sales Only') && !auth()->user()->hasPermission('All Sales')), function ($q) {
                $q->where('sales.salesperson_id', auth()->id());
            })
            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('sales.id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('sales.invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%')
                                ->orWhere('address', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($salespersonId, function ($q) use ($salespersonId) {
                $q->where('sales.salesperson_id', $salespersonId);
            })
            ->when($deliveryStatus, function ($q) use ($deliveryStatus) {
                $q->where('sales.delivery_status', $deliveryStatus);
            })
            ->when($routeId, function ($q) use ($routeId) {
                $q->whereHas('customer', fn ($c) => $c->where('route_id', $routeId));
            })
            ->when($paymentStatus === 'paid', function ($q) use ($netDueSql) {
                $q->whereRaw("($netDueSql) <= 0.009");
            })
            ->when($paymentStatus === 'partial', function ($q) use ($netDueSql) {
                $q->whereRaw("($netDueSql) > 0.009 AND sales.paid_amount > 0.009");
            })
            ->when($paymentStatus === 'due', function ($q) use ($netDueSql) {
                $q->whereRaw("($netDueSql) > 0.009 AND sales.paid_amount <= 0.009");
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('sales.sale_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sales.sale_date', '<=', $dateTo))
            ->orderByRaw("CASE WHEN sales.status = 'pending' THEN 0 ELSE 1 END ASC")
            ->orderByRaw('sales.sale_date DESC')
            ->orderByRaw('sales.id DESC')
            ->groupBy('sales.id');
    }



    public function exportPdfAllReturns(Request $request)
    {
        $salesReturns = $this->filteredSalesReturnsQuery($request)->get();
        $salesReturns->load(['sale.customer', 'sale.saleReturns']);
        $generalSetting = GeneralSetting::first();

        $type = $request->input('type');
        $isBatchPrint = ($request->has('ids') || $type === 'batch') && $type !== 'total';
        $view = $isBatchPrint ? 'sales.pdf.returns-batch' : 'sales.pdf.all-returns';
        $orientation = $isBatchPrint ? 'portrait' : 'landscape';

        $pdfService = app(PdfService::class);
        $data = [
            'salesReturns' => $salesReturns,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'all-sales-returns-'.time().'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream($view, $data, $filename, $orientation);
        }

        return $pdfService->download($view, $data, $filename, $orientation);
    }

    public function exportCsvAllReturns(Request $request): StreamedResponse
    {
        $salesReturns = $this->filteredSalesReturnsQuery($request)->get();
        $filename = 'all-sales-returns-'.time().'.csv';

        return response()->streamDownload(function () use ($salesReturns) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Return Invoice No.',
                'Date',
                'Customer',
                'Mobile',
                'Total Amount',
                'Discount',
                'Payable',
                'Paid',
                'Due',
            ]);
            foreach ($salesReturns as $sr) {
                fputcsv($out, [
                    $sr->return_invoice_no,
                    $sr->return_date->format('m/d/Y'),
                    $sr->sale->customer->name,
                    $sr->sale->customer->phone ?? '',
                    formatCurrency($sr->subtotal),
                    formatCurrency($sr->discount),
                    formatCurrency($sr->payable_amount),
                    formatCurrency($sr->paid_amount),
                    formatCurrency($sr->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredSalesReturnsQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $customerId = $request->input('customer_id');
        $status = $request->input('status');
        $ids = $request->input('ids');

        $user = $request->user();
        $isSalesperson = $user && $user->hasRole('salesperson');

        return SaleReturn::query()
            ->with('sale.customer')
            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($isSalesperson, function ($q) use ($user) {
                $q->whereHas('sale', function ($sq) use ($user) {
                    $sq->where('salesperson_id', $user->id);
                });
            })
            ->when($customerId && !$ids, function ($q) use ($customerId) {
                $q->whereHas('sale', function ($sq) use ($customerId) {
                    $sq->where('customer_id', $customerId);
                });
            })
            ->when($status && !$ids, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search !== '' && !$ids, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('sale.customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom && !$ids, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo && !$ids, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByRaw('return_date DESC')
            ->orderByRaw('id DESC');
    }

    public function storeReturnPayment(Request $request, SaleReturn $saleReturn): RedirectResponse
    {
        $user = $request->user();
        if (! $user || (! $user->hasPermission('Store Customer Payment') && ! $user->hasRole('salesperson'))) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'receiving_amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,check'],
            'check_number' => ['required_if:payment_method,check', 'nullable', 'string', 'max:255'],
            'check_date' => ['required_if:payment_method,check', 'nullable', 'date'],
        ]);

        // Compute effective cash due using progressive ledger offsets across all returns for this sale
        $saleReturn->load(['sale.saleReturns' => function ($q) {
            $q->orderBy('id', 'asc');
        }]);
        $remainingUnpaid = max(0, (float)$saleReturn->sale->receivable_amount - (float)$saleReturn->sale->paid_amount);
        $effectiveDue = 0;

        foreach ($saleReturn->sale->saleReturns as $sr) {
            $payable = (float)$sr->payable_amount;
            $offset = min($payable, $remainingUnpaid);
            $cashOwed = $payable - $offset;
            $remainingUnpaid -= $offset;
            
            if ($sr->id === $saleReturn->id) {
                $effectiveDue = max(0, $cashOwed - (float)$sr->paid_amount);
                break;
            }
        }

        $paying = (float) $validated['receiving_amount'];
        if ($effectiveDue < 0.001) {
            return back()->withErrors(['receiving_amount' => 'No cash refund is due. The return has been offset against the customer\'s outstanding balance.']);
        }
        if ($paying > $effectiveDue + 0.0001) {
            return back()->withErrors(['receiving_amount' => 'Amount cannot exceed the cash refund due ('.formatCurrency($effectiveDue).').']);
        }

        DB::transaction(function () use ($saleReturn, $paying, $request, $validated) {
            SaleReturnPayment::create([
                'sale_return_id' => $saleReturn->id,
                'user_id' => $request->user()?->id,
                'amount' => $paying,
                'payment_method' => $validated['payment_method'],
                'check_number' => $validated['check_number'] ?? null,
                'check_date' => $validated['check_date'] ?? null,
            ]);
            $saleReturn->increment('paid_amount', $paying);
        });

        return back()->with('success', 'Payment received successfully.');
    }

    public function exportPdfReturn(SaleReturn $saleReturn, Request $request)
    {
        if ($saleReturn->status !== 'confirmed') {
            return redirect()->route('sales.return')->with('error', 'Sale returns can only be printed or downloaded once confirmed.');
        }

        $saleReturn->load(['sale.customer', 'items.product.unit', 'items.batches.productBatch']);
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'saleReturn' => $saleReturn,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'return-'.$saleReturn->return_invoice_no.'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('sales.pdf.return-invoice', $data, $filename);
        }

        return $pdfService->download('sales.pdf.return-invoice', $data, $filename);
    }
}
