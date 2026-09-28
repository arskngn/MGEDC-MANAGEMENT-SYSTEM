<?php

namespace App\Services;

use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Events\AdjustmentCreated;
use App\Enums\AdjustmentType;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;
use App\Services\BatchManagementService;

class AdjustmentService
{
    public function __construct(protected BatchManagementService $batchService)
    {
    }

    /**
     * Store a new adjustment.
     */
    public function createAdjustment(array $data): Adjustment
    {
        $adjustment = DB::transaction(function () use ($data) {
            $adjustment = Adjustment::create([
                'tracking_no' => $data['tracking_no'],
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                $qty = (float)$item['quantity'];
                $batchId = $item['product_batch_id'] ?? null;

                // Handle non-batch-tracked products
                if (!$product->requires_batch_tracking) {
                    $batchNumber = 'DEFAULT-' . $product->id . '-' . $product->id;
                    $batch = ProductBatch::where([
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                    ])->first();

                    if (!$batch && $item['type'] === AdjustmentType::Subtraction->value) {
                        throw new InsufficientStockException($product, "Insufficient stock for adjustment.");
                    }

                    if ($item['type'] === AdjustmentType::Subtraction->value && (!$batch || $batch->quantity_available < $qty)) {
                        throw new InsufficientStockException($product, "Insufficient stock for adjustment.");
                    }

                    if (!$batch) {
                        // Create default batch if it doesn't exist and we're adding
                        $batch = ProductBatch::create([
                            'product_id' => $product->id,
                            'batch_number' => $batchNumber,
                            'quantity' => 0,
                            'quantity_available' => 0,
                        ]);
                    }

                    $batchId = $batch->id;
                } else if (!$batchId) {
                    // For batch-tracked products, require a batch
                    throw new \Exception("Batch selection is required for product: {$product->name}");
                }

                $unitCost = (float) ($product->fixed_price ?? 0);
                $subtotalCost = $unitCost * $qty;

                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $batchId,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal_cost' => $subtotalCost,
                    'type' => $item['type'],
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                ]);
            }

            return $adjustment;
        });

        event(new AdjustmentCreated($adjustment));

        return $adjustment;
    }

    /**
     * Update an existing adjustment.
     */
    public function updateAdjustment(Adjustment $adjustment, array $data): Adjustment
    {
        return DB::transaction(function () use ($adjustment, $data) {
            $adjustment->update([
                'tracking_no' => $data['tracking_no'],
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'] ?? null,
                'note' => $data['note'] ?? null,
            ]);

            // Re-validate stock before deleting old items
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if (!$product) continue;

                $existingItem = $adjustment->items->where('product_id', $product->id)->first();
                $existingEffect = 0;
                if ($existingItem) {
                    $existingEffect = ($existingItem->type === AdjustmentType::Addition->value) 
                        ? (float)$existingItem->quantity 
                        : -(float)$existingItem->quantity;
                }

                $availableStock = (float)$product->current_stock - $existingEffect;
                
                if ($item['type'] === AdjustmentType::Subtraction->value && $availableStock < (float)$item['quantity']) {
                    throw new InsufficientStockException($product);
                }
            }

            // Reload items fresh from DB, delete each (fires observer), then clear stale relation
            $adjustment->load('items')->items->each->delete();
            $adjustment->unsetRelation('items');

            // Create new items
            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                $batchId = $item['product_batch_id'] ?? null;

                // Handle non-batch-tracked products
                if (!$product->requires_batch_tracking) {
                    $batchNumber = 'DEFAULT-' . $product->id . '-' . $product->id;
                    $batch = ProductBatch::where([
                        'product_id' => $product->id,
                        'batch_number' => $batchNumber,
                    ])->first();

                    if (!$batch) {
                        $batch = ProductBatch::create([
                            'product_id' => $product->id,
                            'batch_number' => $batchNumber,
                            'quantity' => 0,
                            'quantity_available' => 0,
                        ]);
                    }

                    $batchId = $batch->id;
                }

                $qty = (float) $item['quantity'];
                $unitCost = (float) ($product->fixed_price ?? 0);
                $subtotalCost = $unitCost * $qty;

                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $batchId,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal_cost' => $subtotalCost,
                    'type' => $item['type'],
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                ]);
            }

            $adjustment->load('items');

            return $adjustment;
        });
    }

    /**
     * Delete an adjustment.
     */
    public function deleteAdjustment(Adjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            // Reload items fresh from DB, delete each (fires observer), then clear stale relation
            $adjustment->load('items')->items->each->delete();
            $adjustment->unsetRelation('items');
            $adjustment->delete();
        });
    }
}
