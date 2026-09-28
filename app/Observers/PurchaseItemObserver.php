<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Models\ProductBatch;

class PurchaseItemObserver
{
    /**
     * Handle the PurchaseItem "created" event.
     */
    public function created(PurchaseItem $purchaseItem): void
    {
        $existingBatch = ProductBatch::query()
            ->where('purchase_item_id', $purchaseItem->id)
            ->first();

        if ($existingBatch) {
            $existingBatch->update([
                'quantity' => $existingBatch->quantity + $purchaseItem->quantity,
            ]);
            return;
        }

        ProductBatch::create([
            'product_id' => $purchaseItem->product_id,
            'purchase_id' => $purchaseItem->purchase_id,
            'purchase_item_id' => $purchaseItem->id,
            'batch_number' => 'BATCH-' . $purchaseItem->id,
            'quantity' => $purchaseItem->quantity,
            'quantity_available' => $purchaseItem->quantity,
            'quantity_sold' => 0,
        ]);
    }

    /**
     * Handle the PurchaseItem "updated" event.
     */
    public function updated(PurchaseItem $purchaseItem): void
    {
        //
    }

    /**
     * Handle the PurchaseItem "deleted" event.
     */
    public function deleted(PurchaseItem $purchaseItem): void
    {
        ProductBatch::query()
            ->where('purchase_item_id', $purchaseItem->id)
            ->delete();
    }

    /**
     * Handle the PurchaseItem "restored" event.
     */
    public function restored(PurchaseItem $purchaseItem): void
    {
        //
    }

    /**
     * Handle the PurchaseItem "force deleted" event.
     */
    public function forceDeleted(PurchaseItem $purchaseItem): void
    {
        //
    }
}
