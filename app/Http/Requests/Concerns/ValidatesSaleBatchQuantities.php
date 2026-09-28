<?php

namespace App\Http\Requests\Concerns;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use Illuminate\Contracts\Validation\Validator;

trait ValidatesSaleBatchQuantities
{
    protected function validateSaleBatchQuantities(Validator $validator): void
    {
        $sale = $this->route('sale');
        $releasedByBatch = [];

        if ($sale instanceof Sale) {
            $sale->loadMissing('items.batches');
            foreach ($sale->items as $saleItem) {
                foreach ($saleItem->batches as $saleItemBatch) {
                    $batchId = $saleItemBatch->product_batch_id;
                    $releasedByBatch[$batchId] = ($releasedByBatch[$batchId] ?? 0) + (float) $saleItemBatch->quantity;
                }
            }
        }

        foreach ($this->input('items', []) as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $requestedQty = (float) ($item['quantity'] ?? 0);
            
            // Validate overall quantity against available stock
            if ($productId > 0 && $requestedQty > 0) {
                $product = Product::find($productId);
                if ($product) {
                    // Calculate total available stock across all active batches
                    $availableStock = ProductBatch::query()
                        ->where('product_id', $productId)
                        ->where('status', 'active')
                        ->sum('quantity_available');
                    
                    // Add back any stock released by editing this sale
                    if ($sale instanceof Sale) {
                        $saleItem = $sale->items->firstWhere('product_id', $productId);
                        if ($saleItem) {
                            foreach ($saleItem->batches as $saleItemBatch) {
                                $availableStock += (float) $saleItemBatch->quantity;
                            }
                        }
                    }

                    if ($requestedQty > $availableStock + 0.0001) {
                        $validator->errors()->add(
                            "items.$index.quantity",
                            "Requested quantity ({$requestedQty}) exceeds available stock ({$availableStock})."
                        );
                    }
                }
            }

            if ($this->saleItemUsesAutomaticBatchAllocation($item)) {
                continue;
            }

            $allocations = [];

            foreach ($item['batches'] ?? [] as $batchRow) {
                $batchId = (int) ($batchRow['batch_id'] ?? 0);
                $qty = (float) ($batchRow['quantity'] ?? 0);
                if ($batchId <= 0 || $qty <= 0) {
                    continue;
                }
                $allocations[$batchId] = ($allocations[$batchId] ?? 0) + $qty;
            }

            foreach ($allocations as $batchId => $allocatedQty) {
                $batch = ProductBatch::query()
                    ->where('id', $batchId)
                    ->where('product_id', $productId)
                    ->first();

                if (! $batch) {
                    $validator->errors()->add(
                        "items.$index.batches",
                        'One or more selected batches are invalid for this product.'
                    );

                    continue;
                }

                $available = (float) $batch->quantity_available + (float) ($releasedByBatch[$batchId] ?? 0);

                if ($allocatedQty > $available + 0.0001) {
                    $validator->errors()->add(
                        "items.$index.batches",
                        "Quantity for batch {$batch->batch_number} exceeds available stock ({$available})."
                    );
                }
            }
        }
    }

    protected function saleItemUsesAutomaticBatchAllocation(array $item): bool
    {
        if (isset($item['use_fefo'])) {
            return (string) $item['use_fefo'] === '1';
        }

        if (isset($item['use_fifo'])) {
            return (string) $item['use_fifo'] === '1';
        }

        return true;
    }
}
