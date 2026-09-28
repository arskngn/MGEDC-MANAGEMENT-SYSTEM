<?php

namespace App\Actions\Inventory;

use App\Models\StockLedgerEntry;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogStockTransactionAction
{
    /**
     * Log a stock transaction to the append-only ledger and update the running balance.
     *
     * @param Product $product
     * @param float $quantityChange Positive for inward, negative for outward
     * @param string $transactionType e.g., 'sale', 'purchase', 'adjustment'
     * @param Model|null $reference e.g., SaleItem, PurchaseItem
     * @param int|null $productBatchId
     * @param string|null $notes
     * @return StockLedgerEntry
     */
    public function execute(
        Product $product,
        float $quantityChange,
        string $transactionType,
        ?Model $reference = null,
        ?int $productBatchId = null,
        ?string $notes = null
    ): StockLedgerEntry {
        return DB::transaction(function () use ($product, $quantityChange, $transactionType, $reference, $productBatchId, $notes) {
            // Get the last running balance for this product
            $lastEntry = StockLedgerEntry::query()
                ->where('product_id', '=', $product->id)
                ->lockForUpdate() // Prevent concurrent race conditions
                ->orderByDesc('id')
                ->first();

            $previousBalance = $lastEntry ? (float)$lastEntry->running_balance : 0.0;
            $newBalance = $previousBalance + $quantityChange;

            $referenceId = null;
            $referenceType = null;
            if ($reference) {
                $referenceId = $reference->id;
                $referenceType = get_class($reference);
            }

            $entry = StockLedgerEntry::create([
                'product_id' => $product->id,
                'product_batch_id' => $productBatchId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'transaction_type' => $transactionType,
                'quantity_change' => $quantityChange,
                'running_balance' => $newBalance,
                'notes' => $notes,
            ]);

            // Now explicitly update the product's overall current_stock cache
            $this->updateProductAggregateStock($product->id);

            return $entry;
        });
    }

    /**
     * Update the cached `current_stock` column from active batch quantities.
     */
    protected function updateProductAggregateStock(int $productId): void
    {
        $product = Product::query()->find($productId);

        if ($product) {
            $product->updateStock();
        }
    }
}
