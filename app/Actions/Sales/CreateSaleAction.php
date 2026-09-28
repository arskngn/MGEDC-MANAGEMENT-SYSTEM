<?php

namespace App\Actions\Sales;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemBatch;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Events\SaleCreated;
use App\Exceptions\InsufficientStockException;
use App\Services\BatchManagementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateSaleAction
{
    public function __construct(
        protected BatchManagementService $batchService,
        protected CalculateSaleTotalsAction $calculateTotalsAction
    ) {
    }

    /**
     * Store a new sale explicitly without relying on Observers for stock updates.
     */
    public function execute(array $data): Sale
    {
        $sale = DB::transaction(function () use ($data) {
            $discount = (float) ($data['discount'] ?? 0);
            $discountType = $data['discount_type'] ?? 'fixed';

            $totals = $this->calculateTotalsAction->execute($data['items'], $discount, $discountType);

            // Determine sale status based on user role
            $user = Auth::user();
            $status = ($user && $user->hasRole('salesperson')) ? 'pending' : 'confirmed';

            $salespersonId = $data['salesperson_id'] ?? null;
            if (!$salespersonId && $user && $user->hasRole('salesperson')) {
                $salespersonId = $user->id;
            }

            $sale = Sale::create([
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? Auth::id(),
                'salesperson_id' => $salespersonId,
                'sale_date' => $data['sale_date'],
                'note' => $data['note'] ?? null,
                'store_category_id' => $this->resolveStoreCategoryId($data['store_category'] ?? null),
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'discount_type' => $discountType,
                'receivable_amount' => $totals['receivable'],
                'paid_amount' => 0,
                'status' => $status,
            ]);

            $affectedProductIds = [];

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
                // Double-check: if we have manual batches, use_fefo must be false
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

                // Keep track of products modified so we can update stock explicitly
                $affectedProductIds[] = $product->id;
            }

            // Now log the ledger entry
            // Wait, we deducted from multiple batches, so we can log an entry for the total sale
            // Or log one ledger entry per batch. Let's log ONE ledger entry for the total sale item for the product
            // Wait, the action already deducts from batches. Let's log it per item.
            
            // Re-aggregate and update current stock from the new ledger
            $affectedProductIds = array_unique($affectedProductIds);
            $logAction = app(\App\Actions\Inventory\LogStockTransactionAction::class);
            
            // For each item, we should log the transaction
            foreach ($data['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);
                $logAction->execute(
                    $product,
                    -(float)$itemData['quantity'],
                    'sale',
                    $sale,
                    null, // productBatchId
                    null // notes
                );
            }

            return $sale;
        });

        // Dispatch explicit event for independent processes (emails, logging, CQRS)
        // Wrapped in try-catch so a broadcast/WebSocket failure never crashes the sale
        try {
            event(new SaleCreated($sale));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SaleCreated broadcast failed (sale was saved): ' . $e->getMessage());
        }

        return $sale;
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

            // Use lockForUpdate to prevent race conditions during checkout
            $batch = ProductBatch::where('id', $batchId)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($batch->quantity_available < $batchQty) {
                throw new \Exception("Not enough stock available in batch {$batch->batch_number}. Available: {$batch->quantity_available}, Requested: {$batchQty}");
            }

            // Use DB update without triggering Observers (or if Observers run, it's fine, but explicit is better)
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
