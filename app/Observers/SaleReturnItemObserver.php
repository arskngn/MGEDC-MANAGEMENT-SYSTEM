<?php

namespace App\Observers;

use App\Models\SaleReturnItem;

/**
 * Batch-level stock restoration is handled in SaleReturnService when return batches are recorded.
 */
class SaleReturnItemObserver
{
    public function created(SaleReturnItem $saleReturnItem): void
    {
        // Stock updates are applied per batch in SaleReturnService::applyReturnToBatch().
    }

    public function deleted(SaleReturnItem $saleReturnItem): void
    {
        //
    }
}
