<?php

namespace App\Services;

use App\Actions\Inventory\LogStockTransactionAction;
use App\Models\BatchStockLog;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\SaleItem;
use App\Models\StockLedgerEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BatchManagementService
{
    /**
     * Get available batches for a product, sorted by FEFO.
     */
    public function getAvailableBatchesForSale(int $productId)
    {
        return ProductBatch::where('product_id', $productId)
            ->where('quantity_available', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Log a stock transaction for a batch.
     */
    public function logTransaction(ProductBatch $batch, string $type, float $quantityChange, $reference = null, $userId = null)
    {
        // Batch stock logs table was removed.
        // Stock transactions are now handled exclusively by StockLedgerEntry.
    }

    /**
     * Allocate stock from batches for a sale item using FEFO.
     */
    public function allocateStockForSale(SaleItem $saleItem): void
    {
        $quantityNeeded = $saleItem->quantity;
        $batches = $this->getAvailableBatchesForSale($saleItem->product_id);

        foreach ($batches as $batch) {
            if ($quantityNeeded <= 0) break;

            /** @var ProductBatch $batch */
            $allocateQty = min($quantityNeeded, $batch->quantity_available);

            DB::transaction(function () use ($batch, $allocateQty, $saleItem) {
                /** @var ProductBatch $batch */
                $batch->quantity_available -= $allocateQty;
                $batch->quantity_sold += $allocateQty;
                $batch->save();

                $saleItem->batches()->create([
                    'product_batch_id' => $batch->id,
                    'quantity' => $allocateQty,
                ]);

                $this->logTransaction($batch, 'sold', -$allocateQty, $saleItem);
            });

            $quantityNeeded -= $allocateQty;
        }

        if ($quantityNeeded > 0) {
            // This should ideally be caught by validation before reaching here
            throw new \Exception("Insufficient batch stock for product: " . $saleItem->product->name);
        }
    }

    /**
     * Handle stock return to a batch.
     */
    public function returnStockToBatch(ProductBatch $batch, float $quantity, $reference = null): void
    {
        DB::transaction(function () use ($batch, $quantity, $reference) {
            $batch->quantity_sold = max(0, (float) $batch->quantity_sold - $quantity);
            $batch->quantity_available += $quantity;
            $batch->quantity_returned += $quantity;
            
            if ($batch->quantity_available > 0 && in_array($batch->status, [ProductBatch::STATUS_DEPLETED, 'sold'], true)) {
                $batch->status = ProductBatch::STATUS_ACTIVE;
            }
            
            $batch->save();

            $this->logTransaction($batch, 'returned', $quantity, $reference);
        });
    }

    /**
     * Dispose of damaged or surplus stock.
     * Saves all disposal details to the stock ledger.
     */
    public function disposeStock(ProductBatch $batch, float $quantity, string $type, string $reason, $userId, $method = null)
    {
        return DB::transaction(function () use ($batch, $quantity, $type, $reason, $userId, $method) {
            /** @var ProductBatch $batch */
            $batch->quantity_available -= $quantity;
            $batch->quantity_adjusted  += $quantity;
            $batch->save();

            // Build a descriptive notes string so the ledger entry is human-readable
            $typeLabelMap = [
                'expired'            => 'Expired Stock',
                'damaged_in_storage' => 'Damaged in Storage',
                'spoiled'            => 'Spoiled / Degraded',
                'recall'             => 'Batch Recall',
            ];
            $notesParts = ['Type: ' . ($typeLabelMap[$type] ?? $type)];
            if (!empty($method)) {
                $notesParts[] = 'Method: ' . $method;
            }
            if (!empty($reason)) {
                $notesParts[] = 'Reason: ' . $reason;
            }
            $notes = implode(' | ', $notesParts);

            // Write a proper ledger entry so the batch detail page shows this disposal
            $logAction = app(LogStockTransactionAction::class);
            $logAction->execute(
                $batch->product,
                -$quantity,          // negative = stock going out
                'adjusted',
                null,                // no morph reference for a manual disposal
                $batch->id,
                $notes
            );
        });
    }
}
