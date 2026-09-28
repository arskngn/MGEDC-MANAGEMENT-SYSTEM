<?php

namespace App\Listeners;

use App\Events\ModelChanged;
use App\Models\AdjustmentItem;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\CachedSettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvalidateSystemCache implements ShouldQueue
{
    public bool $afterCommit = true;

    /**
     * Handle the event.
     *
     * Automatically invalidates all related caches whenever a model is
     * created, updated, or deleted. This makes the system "reactive" —
     * changes are immediately reflected on all pages.
     */
    public function handle(ModelChanged $event): void
    {
        $model = $event->model;
        $modelClass = get_class($model);

        // ── Dashboard Stats ──
        // These models affect the dashboard summary cards & transaction stats.
        $dashboardModels = [
            Product::class,
            Customer::class,
            Supplier::class,
            Category::class,
            Sale::class,
            SaleReturn::class,
            Purchase::class,
            PurchaseReturn::class,
            Expense::class,
            ExpenseType::class,
        ];

        if (in_array($modelClass, $dashboardModels)) {
            \App\Jobs\CalculateDashboardStats::dispatch();
        }

        // ── Lookup Caches (Categories, Brands, Units) ──
        // Used in product create/edit dropdowns across the system.
        $lookupModels = [
            Category::class,
            Brand::class,
            Unit::class,
        ];

        if (in_array($modelClass, $lookupModels)) {
            if (function_exists('invalidateLookupCaches')) {
                invalidateLookupCaches();
            }
        }

        // ── General Settings Cache ──
        // Currency, app name, logo, records_per_page, etc.
        if ($model instanceof GeneralSetting) {
            CachedSettingService::invalidate();
            // Also clear currency helper caches
            Cache::forget('general_setting_currency_symbol');
            Cache::forget('general_setting_currency_format');
            Cache::forget('general_setting_currency_name');
        }

        // ── Product Stock Cache ──
        // If a Product itself is updated, clear its global stock cache.
        if ($model instanceof Product) {
            Cache::forget("stock_{$model->id}");
        }


        // ── Stock Item Movements ──
        // Purchase/Sale/Return/Adjustment/Transfer items affect stock levels.
        $stockModels = [
            PurchaseItem::class,
            PurchaseReturnItem::class,
            SaleItem::class,
            SaleReturnItem::class,
            AdjustmentItem::class,
        ];

        if (in_array($modelClass, $stockModels)) {
            $productId = $model->product_id;

            if (!$productId) return;

            // Dispatch job to recalculate summary stats in the background
            \App\Jobs\CalculateDashboardStats::dispatch();

            Cache::forget("stock_{$productId}");
        }
    }

}
