<?php

namespace App\Observers;

use App\Models\SaleItem;
use App\Models\SaleItemBatch;
use App\Services\BatchManagementService;

class SaleItemObserver
{
    public function __construct(protected BatchManagementService $batchService)
    {
    }

    /**
     * Handle the SaleItem "created" event.
     * NOTE: Batch allocation is handled manually in SaleService to support both FEFO and manual selection
     */
    public function created(SaleItem $saleItem): void
    {
        // Do not auto-allocate, since SaleService already handles both FEFO and manual batch selection
    }

    /**
     * Handle the SaleItem "deleted" event.
     * Restore ProductBatch quantity_sold from the specific batches used in this sale
     */
    public function deleted(SaleItem $saleItem): void
    {
        // Get all batch allocations for this specific sale item
        $allocations = SaleItemBatch::where('sale_item_id', $saleItem->id)->get();

        /** @var SaleItemBatch $allocation */
        foreach ($allocations as $allocation) {
            $batch = $allocation->productBatch;
            if ($batch) {
                $this->batchService->returnStockToBatch($batch, $allocation->quantity, $saleItem);
            }
            
            // Delete the allocation record
            $allocation->delete();
        }
    }

    /**
     * Handle the SaleItem "updated" event.
     * NOTE: Updates are handled manually in SaleService::updateSale
     */
    public function updated(SaleItem $saleItem): void
    {
        // Do not auto-handle updates, since SaleService::updateSale does full revert/recreate
    }
}
