<?php

namespace App\Providers;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Adjustment;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\SalePayment;
use App\Models\PurchasePayment;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Models\StoreCategory;
use App\Services\CachedSettingService;
use App\Observers\SaleReturnItemObserver;
use App\Observers\AdjustmentItemObserver;
use App\Observers\GeneralObserver;
use App\Observers\StockItemObserver;
use App\Observers\PurchaseItemObserver;
use App\Observers\ProductBatchObserver;
use App\Policies\SalePolicy;
use App\Policies\PurchasePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        View::composer('*', function ($view) {
            if ($view->offsetExists('generalSetting')) {
                return;
            }

            $view->with('generalSetting', CachedSettingService::get());
        });



        // Register Policies
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);

        // Register Observers — every model that affects caches, broadcasts live updates, or needs audit logging
        Product::observe(GeneralObserver::class);
        Sale::observe(GeneralObserver::class);
        Purchase::observe(GeneralObserver::class);
        Customer::observe(GeneralObserver::class);
        Supplier::observe(GeneralObserver::class);
        Adjustment::observe(GeneralObserver::class);
        Expense::observe(GeneralObserver::class);
        ExpenseType::observe(GeneralObserver::class);
        PurchaseReturn::observe(GeneralObserver::class);
        SaleReturn::observe(GeneralObserver::class);
        SalePayment::observe(GeneralObserver::class);
        PurchasePayment::observe(GeneralObserver::class);
        Category::observe(GeneralObserver::class);
        Brand::observe(GeneralObserver::class);
        Unit::observe(GeneralObserver::class);
        StoreCategory::observe(GeneralObserver::class);
        GeneralSetting::observe(GeneralObserver::class);
        \App\Models\User::observe(GeneralObserver::class);
        \App\Models\Role::observe(GeneralObserver::class);
        \App\Models\StaffTarget::observe(GeneralObserver::class);
        \App\Models\StockLedgerEntry::observe(GeneralObserver::class);

        // Register Item Observers for Cache Invalidation & Logging & Live Broadcast
        PurchaseItem::observe(GeneralObserver::class);
        PurchaseReturnItem::observe(GeneralObserver::class);
        SaleItem::observe(GeneralObserver::class);
        SaleReturnItem::observe(GeneralObserver::class);
        AdjustmentItem::observe(GeneralObserver::class);
        ProductBatch::observe(GeneralObserver::class);
        ProductBatch::observe(ProductBatchObserver::class);

        // PurchaseItemObserver removed - batches only created when items are received

        // Register Stock Observers
        PurchaseItem::observe(StockItemObserver::class);
        PurchaseReturnItem::observe(StockItemObserver::class);
        SaleReturnItem::observe(StockItemObserver::class);
        SaleReturnItem::observe(SaleReturnItemObserver::class);
        AdjustmentItem::observe(StockItemObserver::class);
        AdjustmentItem::observe(AdjustmentItemObserver::class);
    }
}
