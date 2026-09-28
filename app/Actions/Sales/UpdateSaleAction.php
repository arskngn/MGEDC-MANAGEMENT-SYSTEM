<?php

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemBatch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Exceptions\InsufficientStockException;
use App\Services\BatchManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateSaleAction
{
    public function __construct(
        protected BatchManagementService $batchService,
        protected CalculateSaleTotalsAction $calculateTotalsAction
    ) {
    }

    /**
     * Update an existing sale and explicitly handle stock changes.
     */
    public function execute(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            $affectedProductIds = [];

            // 1. Revert stock and COGS for existing sale items
            $logAction = app(\App\Actions\Inventory\LogStockTransactionAction::class);
            
            foreach ($sale->items as $oldItem) {
                foreach ($oldItem->batches as $saleItemBatch) {
                    $batch = ProductBatch::find($saleItemBatch->product_batch_id);
                    if ($batch) {
                        // Use explicit update to avoid firing Observers
                        ProductBatch::where('id', $batch->id)->update([
                            'quantity_available' => $batch->quantity_available + $saleItemBatch->quantity,
                            'quantity_sold' => $batch->quantity_sold - $saleItemBatch->quantity,
                        ]);
                        $this->batchService->logTransaction($batch, 'sale_revert', $saleItemBatch->quantity, $sale, Auth::id());
                    }
                }
                
                // Log revert in the global ledger
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    // Calculate total quantity reverted for this sale item.
                    $revertQty = 0;
                    foreach ($oldItem->batches as $saleItemBatch) {
                        $revertQty += (float) $saleItemBatch->quantity;
                    }
                    // If there were no batch-specific records, fall back to the item's quantity
                    if ($revertQty <= 0) {
                        $revertQty = (float) $oldItem->quantity;
                    }

                    $logAction->execute(
                        $product,
                        $revertQty,
                        'sale_revert',
                        $sale,
                        null,
                        null
                    );
                }
                
                $affectedProductIds[] = $oldItem->product_id;
                $oldItem->batches()->delete();
            }

            $sale->items()->delete();

            // 2. Re-calculate totals
            $discount = (float) ($data['discount'] ?? 0);
            $discountType = $data['discount_type'] ?? 'fixed';

            $totals = $this->calculateTotalsAction->execute($data['items'], $discount, $discountType);

            $sale->update([
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? $sale->user_id,
                'salesperson_id' => array_key_exists('salesperson_id', $data) ? $data['salesperson_id'] : $sale->salesperson_id,
                'sale_date' => $data['sale_date'],
                'note' => $data['note'] ?? $sale->note,
                'store_category_id' => $this->resolveStoreCategoryId($data['store_category'] ?? null),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'discount_type' => $discountType,
                'receivable_amount' => $totals['receivable'],
                'due_amount' => $totals['receivable'] - $sale->paid_amount,
            ]);

            // 3. Process new items
            foreach ($data['items'] as $itemData) {
                $product = Product::where('id', $itemData['product_id'])->lockForUpdate()->firstOrFail();
                $quantityToSell = (float)$itemData['quantity'];
                
                // If there are manual batches, force use_fefo to false
                $hasManualBatches = isset($itemData['batches']) && count($itemData['batches']) > 0;
                $requiresBatchTracking = (bool) $product->requires_batch_tracking;
                
                if (!$requiresBatchTracking) {
                    // Non-batch products always use automatic allocation
                    $useFefo = true;
                } else {
                    $useFefo = $hasManualBatches ? false : $this->usesAutomaticBatchAllocation($itemData);
                }
                
                if ($useFefo) {
                    $cogsResult = $this->deductStockAndCalculateCogs(
                        $product,
                        $quantityToSell,
                        $sale
                    );
                } else {
                    $cogsResult = $this->deductFromSelectedBatches(
                        $product,
                        $itemData['batches'] ?? [],
                        $quantityToSell,
                        $sale
                    );
                }
                // Double-check: if we have deductions from specific batches, use_fefo must be false
                if ($requiresBatchTracking && count($itemData['batches'] ?? []) > 0) {
                    $useFefo = false;
                }
                
                $lineDiscountRate = min(max((float) ($itemData['discount_rate'] ?? 0), 0), 100);
                $lineUnitDiscount = max((float) ($itemData['unit_discount'] ?? 0), 0);
                $lineUnitDiscountType = $itemData['unit_discount_type'] ?? 'fixed';
                $lineSubTotal = round($quantityToSell * (float)$itemData['unit_price'], 2);
                if ($lineUnitDiscountType === 'percentage') {
                    $lineUnitDiscount = min($lineUnitDiscount, 100);
                    $lineUnitDiscountAmount = round($lineSubTotal * $lineUnitDiscount / 100, 2);
                } else {
                    $lineUnitDiscountAmount = round($quantityToSell * $lineUnitDiscount, 2);
                }
                $lineDiscountType = $itemData['discount_type'] ?? 'fixed';
                if ($lineDiscountType === 'percentage') {
                    $lineDiscountAmount = round($lineSubTotal * $lineDiscountRate / 100, 2);
                } else {
                    $lineDiscountAmount = round($lineDiscountRate, 2);
                }
                $lineTotal = round(max(0, $lineSubTotal - $lineUnitDiscountAmount - $lineDiscountAmount), 2);

                $saleItem = SaleItem::create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantityToSell,
                    'unit_label' => $product->unit->short_name ?? $product->unit->name,
                    'unit_price' => $itemData['unit_price'],
                    'unit_discount' => $lineUnitDiscount,
                    'unit_discount_type' => $lineUnitDiscountType,
                    'discount_rate' => $lineDiscountRate,
                    'discount_type' => $lineDiscountType,
                    'line_total' => $lineTotal,
                    'cost_price' => $cogsResult['cost_price'],
                    'sale_id' => $sale->id,
                    'use_fefo' => $useFefo,
                    'is_free' => !empty($itemData['is_free']),
                ]);

                foreach ($cogsResult['deductions'] as $deduction) {
                    SaleItemBatch::create([
                        'sale_item_id' => $saleItem->id,
                        'product_batch_id' => $deduction['batch_id'],
                        'quantity' => $deduction['quantity'],
                        'cost_price' => $deduction['cost_price_per_unit'],
                    ]);
                }
                
                $affectedProductIds[] = $product->id;
            }

            // 4. EXPLICIT SIDE EFFECTS (Ledger + Recalculate Stock)
            // Log new items
            foreach ($data['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                if ($product) {
                    $logAction->execute(
                        $product,
                        -(float)$itemData['quantity'],
                        'sale',
                        $sale,
                        null, // productBatchId
                        null // notes
                    );
                }
            }

            return $sale;
        });
    }

    protected function resolveStoreCategoryId(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        $normalized = trim(preg_replace('/\s+/', ' ', $name) ?? $name);

        $category = \App\Models\StoreCategory::firstOrCreate(['name' => $normalized]);

        return $category->id;
    }

    protected function deductFromSelectedBatches(Product $product, array $selectedBatches, float $quantityToSell, Sale $sale): array
    {
        $totalCost = 0;
        $totalDeducted = 0;
        $deductions = [];

        foreach ($selectedBatches as $batchData) {
            $batchId = $batchData['batch_id'] ?? null;
            $batchQty = (float)($batchData['quantity'] ?? 0);
            
            if (!$batchId || $batchQty <= 0) continue;

            $batch = ProductBatch::where('id', $batchId)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($batch->quantity_available < $batchQty) {
                throw new \Exception("Not enough stock available in batch {$batch->batch_number}. Available: {$batch->quantity_available}, Requested: {$batchQty}");
            }

            ProductBatch::where('id', $batch->id)->update([
                'quantity_available' => $batch->quantity_available - $batchQty,
                'quantity_sold' => $batch->quantity_sold + $batchQty,
            ]);

            $costPricePerUnit = (float) (optional($batch->purchaseItem)->unit_price ?? 0);
            $totalCost += $batchQty * $costPricePerUnit;
            $totalDeducted += $batchQty;

            $deductions[] = [
                'batch_id' => $batch->id,
                'quantity' => $batchQty,
                'cost_price_per_unit' => $costPricePerUnit,
            ];

            $this->batchService->logTransaction($batch, 'sold', -$batchQty, $sale, Auth::id());
        }

        if ($totalDeducted < $quantityToSell - 0.0001) {
            throw new \Exception("Total quantity from selected batches ({$totalDeducted}) is less than required quantity ({$quantityToSell})");
        }

        $averageCostPrice = $totalDeducted > 0 ? $totalCost / $totalDeducted : 0;

        return [
            'cost_price' => $averageCostPrice,
            'deductions' => $deductions,
        ];
    }

    protected function usesAutomaticBatchAllocation(array $itemData): bool
    {
        if (isset($itemData['use_fefo'])) {
            return (string) $itemData['use_fefo'] === '1';
        }
        if (isset($itemData['use_fifo'])) {
            return (string) $itemData['use_fifo'] === '1';
        }
        return true;
    }

    protected function deductStockAndCalculateCogs(Product $product, float $quantityToSell, Sale $sale): array
    {
        $batches = \App\Models\ProductBatch::where('product_id', $product->id)
            ->where('quantity_available', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $remainingToSell = $quantityToSell;
        $totalCost = 0;
        $deductions = [];

        foreach ($batches as $batch) {
            /** @var \App\Models\ProductBatch $batch */
            if ($remainingToSell <= 0) break;

            $deductQty = min($remainingToSell, $batch->quantity_available);

            ProductBatch::where('id', $batch->id)->update([
                'quantity_available' => $batch->quantity_available - $deductQty,
                'quantity_sold' => $batch->quantity_sold + $deductQty,
            ]);

            $costPricePerUnit = (float) (optional($batch->purchaseItem)->unit_price ?? 0);
            $totalCost += $deductQty * $costPricePerUnit;
            $remainingToSell -= $deductQty;

            $deductions[] = [
                'batch_id' => $batch->id,
                'quantity' => $deductQty,
                'cost_price_per_unit' => $costPricePerUnit,
            ];

            $this->batchService->logTransaction($batch, 'sold', -$deductQty, $sale, Auth::id());
        }

        if ($remainingToSell > 0) {
            throw new InsufficientStockException($product, "Insufficient batch stock for product {$product->name}.");
        }

        $averageCostPrice = $quantityToSell > 0 ? $totalCost / $quantityToSell : 0;

        return [
            'cost_price' => $averageCostPrice,
            'deductions' => $deductions,
        ];
    }
}
