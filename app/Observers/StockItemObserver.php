<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class StockItemObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->updateStock($model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->updateStock($model);
        
        // If product_id changed, update the old product too
        if ($model->wasChanged('product_id')) {
            $oldProductId = $model->getOriginal('product_id');
            if ($oldProductId) {
                $oldProduct = \App\Models\Product::find($oldProductId);
                if ($oldProduct) {
                    $oldProduct->updateStock();
                }
            }
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->updateStock($model);
    }

    /**
     * Update stock for the product related to the model.
     * Uses cache-based distributed locking for safe concurrent updates,
     * avoiding row-level database lock contention.
     */
    protected function updateStock(Model $model): void
    {
        if (isset($model->product_id)) {
            $lock = \Illuminate\Support\Facades\Cache::lock("product_stock_{$model->product_id}", 10);
            
            try {
                // Wait for a maximum of 5 seconds to acquire the lock
                $lock->block(5);
                
                $product = \App\Models\Product::find($model->product_id);
                if ($product) {
                    $product->updateStock();
                }
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                // Log or handle the timeout if necessary
                \Illuminate\Support\Facades\Log::warning("Failed to acquire lock for product {$model->product_id} stock update.");
            } finally {
                $lock?->release();
            }
        }
    }
}
