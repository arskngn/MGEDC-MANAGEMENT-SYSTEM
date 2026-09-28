<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CalculateDashboardStats implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 10;


    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Calculate Summary Stats
        $summary = [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'total_categories' => Category::count(),
        ];
        Cache::put('dashboard_summary_stats', $summary, 86400); // 24 hours

        // 2. Calculate Transaction Stats (Heavy SUM() queries)
        $transactions = [
            'sales' => [
                'count' => Sale::notCancelled()->count(),
                'total_amount' => (float) Sale::notCancelled()->sum('receivable_amount'),
            ],
            'sale_returns' => [
                'count' => SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)->count(),
                'total_amount' => (float) SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)->sum('payable_amount'),
            ],
            'purchases' => [
                'count' => Purchase::count(),
                'total_amount' => (float) Purchase::sum('payable_amount'),
            ],
            'purchase_returns' => [
                'count' => PurchaseReturn::count(),
                'total_amount' => (float) PurchaseReturn::sum('receivable_amount'),
            ],
        ];
        Cache::put('dashboard_transaction_stats', $transactions, 86400); // 24 hours
    }
}
