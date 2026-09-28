<?php

namespace App\Observers;

use App\Models\AdjustmentItem;
use App\Models\ProductBatch;
use App\Enums\AdjustmentType;
use App\Actions\Inventory\LogStockTransactionAction;

class AdjustmentItemObserver
{
    /**
     * Handle the AdjustmentItem "created" event.
     */
    public function created(AdjustmentItem $adjustmentItem): void
    {
        if (!$adjustmentItem->product_batch_id) {
            return;
        }

        $batch = ProductBatch::find($adjustmentItem->product_batch_id);
        if (!$batch) {
            return;
        }

        $qty = (float) $adjustmentItem->quantity;
        $isAddition = ($adjustmentItem->type === AdjustmentType::Addition->value);

        if ($isAddition) {
            // Addition: increase the batch's quantity and available
            $batch->increment('quantity', $qty);
            $batch->increment('quantity_available', $qty);

            $batch->refresh();
            if ($batch->quantity_available > 0 && $batch->status === ProductBatch::STATUS_DEPLETED) {
                $batch->update(['status' => ProductBatch::STATUS_ACTIVE]);
            }
        } else {
            // Subtraction: decrease available and increase sold
            $batch->decrement('quantity_available', $qty);
            $batch->increment('quantity_sold', $qty);
            
            $batch->refresh();
            if ($batch->quantity_available <= 0) {
                $batch->update(['status' => ProductBatch::STATUS_DEPLETED]);
            }
        }

        // Log to Stock Ledger
        $logAction = app(LogStockTransactionAction::class);
        $change = $isAddition ? $qty : -$qty;
        $note = "Adjustment {$adjustmentItem->adjustment?->tracking_no}: {$adjustmentItem->type}";
        if ($adjustmentItem->adjustment?->reason) {
            $note .= " ({$adjustmentItem->adjustment->reason})";
        }

        $logAction->execute(
            product: $adjustmentItem->product,
            quantityChange: $change,
            transactionType: 'adjustment',
            reference: $adjustmentItem,
            productBatchId: $batch->id,
            notes: $note
        );
    }

    /**
     * Handle the AdjustmentItem "deleted" event.
     */
    public function deleted(AdjustmentItem $adjustmentItem): void
    {
        if (!$adjustmentItem->product_batch_id) {
            return;
        }

        $batch = ProductBatch::find($adjustmentItem->product_batch_id);
        if (!$batch) {
            return;
        }

        $qty = (float) $adjustmentItem->quantity;
        $isAddition = ($adjustmentItem->type === AdjustmentType::Addition->value);

        if ($isAddition) {
            // Restore Addition: decrease the batch's quantity and available
            $batch->decrement('quantity', $qty);
            $batch->decrement('quantity_available', $qty);

            $batch->refresh();
            if ($batch->quantity_available <= 0) {
                $batch->update(['status' => ProductBatch::STATUS_DEPLETED]);
            }
        } else {
            // Restore Subtraction: increase available and decrease sold
            $batch->increment('quantity_available', $qty);
            $batch->decrement('quantity_sold', $qty);
            
            $batch->refresh();
            if ($batch->quantity_available > 0 && $batch->status === ProductBatch::STATUS_DEPLETED) {
                $batch->update(['status' => ProductBatch::STATUS_ACTIVE]);
            }
        }

        // Reversal log to Stock Ledger
        $logAction = app(LogStockTransactionAction::class);
        $reversalChange = $isAddition ? -$qty : $qty;
        $note = "Adjustment Reversal {$adjustmentItem->adjustment?->tracking_no}";

        $logAction->execute(
            product: $adjustmentItem->product,
            quantityChange: $reversalChange,
            transactionType: 'adjustment_reversal',
            reference: $adjustmentItem,
            productBatchId: $batch->id,
            notes: $note
        );
    }
}
