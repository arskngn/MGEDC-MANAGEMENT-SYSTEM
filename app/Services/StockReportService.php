<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Cache;

/**
 * StockReportService
 *
 * Handles generation of stock reports with filtering by product.
 * Separates reporting logic from controller to improve testability and reusability.
 */
class StockReportService
{
    /**
     * Get stock report (kept for backward compatibility)
     *
     * @return BaseCollection<array>
     */
    public function getStockByWarehouse(int $warehouseId): BaseCollection
    {
        return $this->getStockReport();
    }

    /**
     * Get stock report filtered by product
     *
     * @param int $productId
     * @return BaseCollection<array>
     */
    public function getStockByProduct(int $productId): BaseCollection
    {
        $product = Product::find($productId);
        if (!$product) {
            return collect();
        }

        $stock = $this->getProductStock($productId);

        return collect([
            [
                'product_id' => $productId,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $stock,
                'alert_quantity' => $product->alert_quantity,
                'unit' => $product->unit?->short_name ?? 'Unit',
                'is_low_stock' => $stock <= $product->alert_quantity,
            ],
        ]);
    }
    
    /**
     * Get stock report for all products
     *
     * @return BaseCollection<array>
     */
    public function getStockReport(): BaseCollection
    {
        $products = Product::query()->orderBy('name')->get();
        $stockData = collect();

        foreach ($products as $product) {
            $stock = $this->getProductStock($product->id);

            $stockData->push([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $stock,
                'alert_quantity' => $product->alert_quantity,
                'unit' => $product->unit?->short_name ?? 'Unit',
                'is_low_stock' => $stock <= $product->alert_quantity,
            ]);
        }

        return $stockData;
    }

    /**
     * Calculate product stock
     *
     * @param int $productId
     * @return float
     */
    public function getProductStock(int $productId): float
    {
        $product = Product::find($productId);
        if (!$product) {
            return 0;
        }

        return $product->getAvailableBatchStock();
    }
    
    /**
     * Calculate product stock for a specific warehouse (kept for backward compatibility)
     *
     * @param int $productId
     * @param int $warehouseId
     * @return float
     */
    public function getProductStockForWarehouse(int $productId, int $warehouseId): float
    {
        return $this->getProductStock($productId);
    }

    /**
     * Get all enabled warehouses
     *
     * @return Collection
     */
    public function getWarehouses(): Collection
    {
        // Warehouses no longer used as a primary filter; return an empty collection for compatibility.
        return collect();
    }

    /**
     * Get all products
     *
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return Product::query()->orderBy('name')->get(['id', 'name', 'sku']);
    }
}
