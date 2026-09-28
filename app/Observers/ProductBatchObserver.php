<?php

namespace App\Observers;

use App\Models\ProductBatch;

class ProductBatchObserver
{
    public function created(ProductBatch $batch): void
    {
        $this->updateProductStock($batch);
    }

    public function updated(ProductBatch $batch): void
    {
        $this->updateProductStock($batch);
    }

    public function deleted(ProductBatch $batch): void
    {
        $this->updateProductStock($batch);
    }

    protected function updateProductStock(ProductBatch $batch): void
    {
        $product = $batch->product;
        if ($product) {
            $product->updateStock();
        }
    }
}
