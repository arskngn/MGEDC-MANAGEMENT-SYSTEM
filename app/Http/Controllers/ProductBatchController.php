<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\ProductBatch;
use App\Models\Product;
use App\Services\BatchManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductBatchController extends Controller
{
    public function __construct(protected BatchManagementService $batchService)
    {
    }

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        
        $batches = ProductBatch::with(['product'])
            ->whereHas('product', fn($q) => $q->where('requires_batch_tracking', true))
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function($q) use ($request) {
                $q->where('batch_number', 'like', "%{$request->search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $products = Product::where('requires_batch_tracking', true)->orderBy('name')->get();

        return view('batches.index', compact('batches', 'products'));
    }

    public function show(ProductBatch $batch): View
    {
        $batch->load(['product', 'stockLedgerEntries']);
        return view('batches.show', compact('batch'));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = Product::findOrFail($request->product_id);

        $rules = [
            'product_id'          => 'required|exists:products,id',
            'batch_number'        => ['nullable', 'string'],
            'quantity'            => 'required|numeric|min:0',
            'status'              => 'required|in:active,quarantined,disposed',
            'expiry_date'         => 'nullable|date|after_or_equal:today',
        ];

        if ($product->requires_batch_tracking) {
            $rules['batch_number'] = [
                'required',
                'string',
                Rule::unique('product_batches')->where(function ($query) use ($request) {
                    return $query->where('product_id', $request->product_id);
                })
            ];
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $product) {
            $validated['quantity_available'] = $validated['quantity'];
            
            // If batch tracking is not required, but a batch number is provided, use it.
            // Otherwise, if batch tracking is not required and no batch number is provided, generate a default.
            if (!$product->requires_batch_tracking && empty($validated['batch_number'])) {
                $validated['batch_number'] = 'N/A-' . \Illuminate\Support\Str::random(8);
            }

            $batch = ProductBatch::create($validated);
            
            $this->batchService->logTransaction($batch, 'received', $validated['quantity'], null, Auth::id());
        });

        return redirect()->route('batches.index')->with('success', 'Batch created successfully.');
    }

    public function update(Request $request, ProductBatch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'batch_number' => [
                'required',
                'string',
                Rule::unique('product_batches')->ignore($batch->id)->where(function ($query) use ($batch) {
                    return $query->where('product_id', $batch->product_id);
                })
            ],
            'status'              => ['required', 'string', 'in:' . implode(',', array_keys(ProductBatch::getStatusOptions()))],
            'expiry_date'         => 'nullable|date|after_or_equal:today',
        ]);

        $batch->update($validated);

        return redirect()->route('batches.index')->with('success', 'Batch updated successfully.');
    }

    public function updateStatus(Request $request, ProductBatch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(ProductBatch::getStatusOptions()))],
        ]);

        $batch->update(['status' => $validated['status']]);

        return redirect()->route('batches.show', $batch)->with('success', 'Batch status updated successfully.');
    }

    public function dispose(Request $request, ProductBatch $batch): RedirectResponse
    {
        $validated = $request->validate([
            'quantity_disposed' => 'required|numeric|min:0.01|max:' . $batch->quantity_available,
            'disposal_type' => 'required|in:expired,damaged_in_storage,spoiled,recall',
            'disposal_method' => 'nullable|string',
            'reason_notes' => 'nullable|string',
        ]);

        $this->batchService->disposeStock(
            $batch,
            $validated['quantity_disposed'],
            $validated['disposal_type'],
            $validated['reason_notes'] ?? '',
            Auth::id(),
            $validated['disposal_method']
        );

        return redirect()->route('batches.index')->with('success', 'Stock disposed successfully.');
    }

    public function destroy(ProductBatch $batch): RedirectResponse
    {
        if ($batch->quantity_sold > 0) {
            return redirect()->route('batches.index')->with('error', 'Cannot delete batch with existing sales.');
        }

        $batch->delete();
        return redirect()->route('batches.index')->with('success', 'Batch deleted successfully.');
    }
}
