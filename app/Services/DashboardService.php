<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get summary statistics for the dashboard.
     */
    public function getSummaryStats(): array
    {
        return Cache::remember('dashboard_summary_stats', 60, function () {
            return [
                'total_products' => Product::count(),
                'total_customers' => Customer::count(),
                'total_suppliers' => Supplier::count(),
                'total_categories' => Category::count(),
            ];
        });
    }


    /**
     * Get products that are low or out of stock.
     *
     * @param int $limit
     * @return array
     */
    public function getStockAlerts(int $limit = 50): array
    {
        return Cache::remember('dashboard_stock_alerts_' . $limit, 60, function () use ($limit) {
            $query = Product::where(function ($q) {
                $q->whereColumn('current_stock', '<=', 'alert_quantity')
                  ->orWhere('current_stock', '<=', 0);
            })->orderBy('current_stock', 'asc');

            $count = $query->count();
            $items = $query->limit($limit)->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'current_stock' => (float) $p->current_stock,
                    'alert_quantity' => (float) $p->alert_quantity,
                ];
            })->toArray();

            return ['count' => $count, 'items' => $items];
        });
    }

    /**
     * Get batches that are expired or expiring within the next N days.
     *
     * @param int $days
     * @param int $limit
     * @return array
     */
    public function getExpiryAlerts(int $days = 30, int $limit = 5): array
    {
        return Cache::remember('dashboard_expiry_alerts_' . $days . '_' . $limit, 60, function () use ($days, $limit) {
            $now = Carbon::now();
            $cutoff = $now->copy()->addDays($days)->toDateString();

            $query = ProductBatch::whereNotNull('expiry_date')
                ->where('expiry_date', '<=', $cutoff)
                ->where('status', ProductBatch::STATUS_ACTIVE)
                ->with('product')
                ->orderBy('expiry_date', 'asc');

            $count = $query->count();
            $items = $query->limit($limit)->get()->map(function ($b) use ($now) {
                $daysLeft = Carbon::parse($b->expiry_date)->startOfDay()->diffInDays($now->startOfDay(), false);
                return [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'product_name' => $b->product?->name ?? 'N/A',
                    'expiry_date' => $b->expiry_date,
                    'days_left' => $daysLeft,
                ];
            })->toArray();

            return ['count' => $count, 'items' => $items];
        });
    }

    /**
     * Get product breakdown and sales performance analytics per category.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $limit
     * @return array
     */
    public function getProductsPerCategory(?string $startDate = null, ?string $endDate = null, int $limit = 10): array
    {
        $cacheKey = 'dashboard_category_analytics_' . ($startDate ?? 'null') . '_' . ($endDate ?? 'null') . '_' . $limit;

        return Cache::remember($cacheKey, 60, function () use ($startDate, $endDate, $limit) {
            $totalProducts = Product::count();

            // Calculate sales per product category within optional date range
            $salesQuery = \App\Models\SaleItem::query()
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->whereNull('sales.deleted_at')
                ->whereNull('products.deleted_at')
                ->whereNotIn('sales.status', ['cancelled', 'rejected'])
                ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED);


        if ($startDate) {
            $salesQuery->whereDate('sales.sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $salesQuery->whereDate('sales.sale_date', '<=', $endDate);
        }

        $salesData = (clone $salesQuery)
            ->select(
                'products.category_id',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(sale_items.line_total), 0) as total_amount')
            )
            ->groupBy('products.category_id')
            ->get()
            ->keyBy(function ($item) {
                return $item->category_id ?? 'uncategorized';
            });

        $totalPeriodSales = (float) $salesData->sum('total_amount');
        $totalPeriodQty = (float) $salesData->sum('total_qty');

        // Query per-product sales summary within specified date range
        $productSalesQuery = \App\Models\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereNull('sales.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED);

        if ($startDate) {
            $productSalesQuery->whereDate('sales.sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $productSalesQuery->whereDate('sales.sale_date', '<=', $endDate);
        }

        $productSalesData = $productSalesQuery
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'products.category_id',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(sale_items.line_total), 0) as total_amount')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.category_id')
            ->orderBy('total_amount', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return $item->category_id ?? 'uncategorized';
            });

        $categories = Category::withCount('products')
            ->withSum('products as total_stock', 'current_stock')
            ->orderBy('products_count', 'desc')
            ->get();

        $items = [];
        foreach ($categories as $c) {
            $catSales = $salesData->get($c->id);
            $qtySold = (float) ($catSales->total_qty ?? 0);
            $salesAmount = (float) ($catSales->total_amount ?? 0);

            $salesPercentage = $totalPeriodSales > 0 
                ? round(($salesAmount / $totalPeriodSales) * 100, 1)
                : ($totalProducts > 0 ? round(($c->products_count / $totalProducts) * 100, 1) : 0);

            $productsList = ($productSalesData->get($c->id) ?? collect())->map(function ($p) {
                return [
                    'product_id' => $p->product_id,
                    'name' => $p->product_name,
                    'sku' => $p->sku,
                    'qty_sold' => (float) $p->total_qty,
                    'total_amount' => (float) $p->total_amount,
                ];
            })->values()->toArray();

            $items[] = [
                'id' => $c->id,
                'name' => $c->name,
                'products_count' => (int) $c->products_count,
                'total_stock' => (float) ($c->total_stock ?? 0),
                'total_sold_qty' => $qtySold,
                'total_sales_amount' => $salesAmount,
                'percentage' => $salesPercentage,
                'products' => $productsList,
            ];
        }

        $uncategorizedQuery = Product::whereNull('category_id');
        $uncategorizedCount = $uncategorizedQuery->count();
        if ($uncategorizedCount > 0) {
            $catSales = $salesData->get('uncategorized');
            $qtySold = (float) ($catSales->total_qty ?? 0);
            $salesAmount = (float) ($catSales->total_amount ?? 0);

            $salesPercentage = $totalPeriodSales > 0 
                ? round(($salesAmount / $totalPeriodSales) * 100, 1)
                : ($totalProducts > 0 ? round(($uncategorizedCount / $totalProducts) * 100, 1) : 0);

            $productsList = ($productSalesData->get('uncategorized') ?? collect())->map(function ($p) {
                return [
                    'product_id' => $p->product_id,
                    'name' => $p->product_name,
                    'sku' => $p->sku,
                    'qty_sold' => (float) $p->total_qty,
                    'total_amount' => (float) $p->total_amount,
                ];
            })->values()->toArray();

            $items[] = [
                'id' => null,
                'name' => 'Uncategorized',
                'products_count' => $uncategorizedCount,
                'total_stock' => (float) $uncategorizedQuery->sum('current_stock'),
                'total_sold_qty' => $qtySold,
                'total_sales_amount' => $salesAmount,
                'percentage' => $salesPercentage,
                'products' => $productsList,
            ];
        }

        if ($totalPeriodSales > 0) {
            usort($items, fn($a, $b) => $b['total_sales_amount'] <=> $a['total_sales_amount']);
        }

            return [
                'total_categories' => Category::count(),
                'total_products' => $totalProducts,
                'total_period_sales' => $totalPeriodSales,
                'total_period_qty' => $totalPeriodQty,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'items' => array_slice($items, 0, $limit),
                'all_items' => $items,
            ];
        });
    }


    /**
     * Get per-date or per-month breakdown for selected category or all categories within a date range.
     */
    public function getCategoryBreakdown(?string $categoryId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $diffDays = $start->diffInDays($end);
        $groupBy = $diffDays <= 31 ? 'date' : 'month';

        if ($groupBy === 'month') {
            $startNorm = $start->copy()->startOfMonth();
            $endNorm = $end->copy()->endOfMonth();
        } else {
            $startNorm = $start->copy();
            $endNorm = $end->copy();
        }

        $selectFormat = $groupBy === 'date' ? '%Y-%m-%d' : '%b %Y';

        $query = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereNull('sales.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->whereBetween('sales.sale_date', [$startNorm->toDateString(), $endNorm->toDateString()]);

        if ($categoryId !== null && $categoryId !== 'ALL') {
            if ($categoryId === 'uncategorized' || $categoryId === 'null') {
                $query->whereNull('products.category_id');
            } else {
                $query->where('products.category_id', (int) $categoryId);
            }
        }

        $amtRows = (clone $query)
            ->selectRaw("DATE_FORMAT(sales.sale_date, '$selectFormat') as label, SUM(sale_items.line_total) as amt")
            ->groupBy('label')
            ->pluck('amt', 'label')
            ->toArray();

        $qtyRows = (clone $query)
            ->selectRaw("DATE_FORMAT(sales.sale_date, '$selectFormat') as label, SUM(sale_items.quantity) as qty")
            ->groupBy('label')
            ->pluck('qty', 'label')
            ->toArray();

        $labels = [];
        $current = $startNorm->copy();
        while ($current <= $endNorm) {
            $lbl = $groupBy === 'date' ? $current->format('Y-m-d') : $current->format('M Y');
            if (!in_array($lbl, $labels)) {
                $labels[] = $lbl;
            }
            $groupBy === 'date' ? $current->addDay() : $current->addMonth();
        }

        $qtys = array_map(fn($l) => (float) ($qtyRows[$l] ?? 0), $labels);
        $amts = array_map(fn($l) => (float) ($amtRows[$l] ?? 0), $labels);

        return [
            'category_id' => $categoryId,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'labels' => $labels,
            'quantities' => $qtys,
            'amounts' => $amts,
            'group_by' => $groupBy,
        ];
    }

    /**
     * Get transaction statistics for sales and purchases.
     */
    public function getTransactionStats(): array
    {
        return Cache::remember('dashboard_transaction_stats', 60, function () {
            $salesCount = Sale::notCancelled()->count();
            $salesTotal = (float) Sale::notCancelled()->sum('receivable_amount');
            $salesDue = (float) Sale::notCancelled()->selectRaw('COALESCE(SUM(GREATEST(0, receivable_amount - paid_amount)), 0) as total_due')->value('total_due');

            $saleReturnsCount = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)->count();
            $saleReturnsTotal = (float) SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)->sum('payable_amount');

            $purchasesCount = Purchase::count();
            $purchasesTotal = (float) Purchase::sum('payable_amount');
            $purchasesDue = (float) Purchase::selectRaw('COALESCE(SUM(GREATEST(0, payable_amount - paid_amount)), 0) as total_due')->value('total_due');

            $purchaseReturnsCount = PurchaseReturn::count();
            $purchaseReturnsTotal = (float) PurchaseReturn::sum('receivable_amount');

            $totalExpenses = (float) \App\Models\Expense::sum('amount');
            $netSales = max(0, $salesTotal - $saleReturnsTotal);
            $netPurchases = max(0, $purchasesTotal - $purchaseReturnsTotal);

            // Calculate Cost of Goods Sold (COGS) for items sold minus items returned
            $cogsSales = (float) \App\Models\SaleItem::query()
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->whereNull('sales.deleted_at')
                ->whereNotIn('sales.status', ['cancelled', 'rejected'])
                ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
                ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(sale_items.cost_price, 0)), 0) as total_cogs')
                ->value('total_cogs');

            $cogsReturns = (float) \App\Models\SaleReturnItem::query()
                ->join('sale_returns', 'sale_return_items.sale_return_id', '=', 'sale_returns.id')
                ->whereNull('sale_returns.deleted_at')
                ->where('sale_returns.status', '!=', SaleReturn::STATUS_REJECTED)
                ->selectRaw('COALESCE(SUM(sale_return_items.return_quantity * COALESCE(sale_return_items.cost_price, 0)), 0) as total_cogs')
                ->value('total_cogs');

            $cogs = max(0, $cogsSales - $cogsReturns);

            // Net Profit = Net Sales - COGS - Total Expenses
            $netProfit = $netSales - $cogs - $totalExpenses;

            return [
                'sales' => [
                    'count' => $salesCount,
                    'total_amount' => $salesTotal,
                    'total_due' => $salesDue,
                ],
                'sale_returns' => [
                    'count' => $saleReturnsCount,
                    'total_amount' => $saleReturnsTotal,
                ],
                'sales_net' => [
                    'count' => max(0, $salesCount - $saleReturnsCount),
                    'total_amount' => $netSales,
                ],
                'purchases' => [
                    'count' => $purchasesCount,
                    'total_amount' => $purchasesTotal,
                    'total_due' => $purchasesDue,
                ],
                'purchase_returns' => [
                    'count' => $purchaseReturnsCount,
                    'total_amount' => $purchaseReturnsTotal,
                ],
                'purchases_net' => [
                    'count' => max(0, $purchasesCount - $purchaseReturnsCount),
                    'total_amount' => $netPurchases,
                ],
                'expenses' => [
                    'total_amount' => $totalExpenses,
                ],
                'cogs' => [
                    'total_amount' => $cogs,
                ],
                'profit' => [
                    'net_profit' => $netProfit,
                ],
                // Top-level aliases for direct Blade/Alpine access
                'total_expenses' => $totalExpenses,
                'cogs_total' => $cogs,
                'net_profit' => $netProfit,
            ];
        });
    }


    /**
     * Get recent transactions for the dashboard.
     */
    public function getRecentSales(int $limit = 5)
    {
        return Sale::with(['customer'])
            ->orderBy('sale_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get aggregated chart data for Purchases & Sales and Purchase & Sales Returns.
     */
    public function getChartData(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subMonths(5)->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        // Calculate differences in days to determine grouping (by day or month)
        $diffDays = $start->diffInDays($end);
        $groupBy = $diffDays <= 31 ? 'date' : 'month';

        // If grouping by month, normalize start/end to full months so labels are whole months
        if ($groupBy === 'month') {
            $start = $start->copy()->startOfMonth();
            $end = $end->copy()->endOfMonth();
        }

        $selectFormat = $groupBy === 'date' ? '%Y-%m-%d' : '%b %Y'; // For MySQL

        // Helper to query and group by date/month
        $getAggregated = function ($modelClass, $dateColumn, $amountColumn) use ($start, $end, $selectFormat) {
            $query = $modelClass::whereBetween($dateColumn, [$start, $end])
                ->selectRaw("DATE_FORMAT($dateColumn, '$selectFormat') as label, SUM($amountColumn) as total")
                ->groupBy('label')
                ->pluck('total', 'label')
                ->toArray();
            return $query;
        };

        $sales = Sale::notCancelled()
            ->whereBetween('sale_date', [$start, $end])
            ->selectRaw("DATE_FORMAT(sale_date, '$selectFormat') as label, SUM(receivable_amount) as total")
            ->groupBy('label')
            ->pluck('total', 'label')
            ->toArray();
        $purchases = $getAggregated(Purchase::class, 'purchase_date', 'payable_amount');
        $salesReturns = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)
            ->whereBetween('return_date', [$start, $end])
            ->selectRaw("DATE_FORMAT(return_date, '$selectFormat') as label, SUM(payable_amount) as total")
            ->groupBy('label')
            ->pluck('total', 'label')
            ->toArray();
        $purchasesReturns = $getAggregated(PurchaseReturn::class, 'return_date', 'receivable_amount');

        // Generate all labels in range
        $labels = [];
        $current = $start->copy();
        while ($current <= $end) {
            $label = $groupBy === 'date' ? $current->format('Y-m-d') : $current->format('M Y');
            if (!in_array($label, $labels)) {
                $labels[] = $label;
            }
            $groupBy === 'date' ? $current->addDay() : $current->addMonth();
        }

        // Fill gaps with 0
        $formatData = function ($data) use ($labels) {
            return array_map(fn($label) => (float) ($data[$label] ?? 0), $labels);
        };

        // Build invoice/ref arrays aligned with labels
        $invoiceMain = [];
        $invoiceReturn = [];
        foreach ($labels as $label) {
            if ($groupBy === 'date') {
                $salesInvoices = Sale::notCancelled()->whereDate('sale_date', $label)->pluck('invoice_no')->toArray();
                $salesReturnInvoices = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)->whereDate('return_date', $label)->pluck('return_invoice_no')->toArray();
            } else {
                // month grouping: parse month label like 'Jul 2026'
                try {
                    $dt = Carbon::createFromFormat('M Y', $label);
                    $salesInvoices = Sale::notCancelled()->whereYear('sale_date', $dt->year)->whereMonth('sale_date', $dt->month)->pluck('invoice_no')->toArray();
                    $salesReturnInvoices = SaleReturn::where('status', '!=', SaleReturn::STATUS_REJECTED)->whereYear('return_date', $dt->year)->whereMonth('return_date', $dt->month)->pluck('return_invoice_no')->toArray();
                } catch (\Exception $e) {
                    $salesInvoices = [];
                    $salesReturnInvoices = [];
                }
            }

            $invoiceMain[] = count($salesInvoices) ? implode(', ', array_slice($salesInvoices, 0, 5)) : '';
            $invoiceReturn[] = count($salesReturnInvoices) ? implode(', ', array_slice($salesReturnInvoices, 0, 5)) : '';
        }

        return [
            'labels' => $labels,
            'sales' => $formatData($sales),
            'purchases' => $formatData($purchases),
            'sales_returns' => $formatData($salesReturns),
            'purchases_returns' => $formatData($purchasesReturns),
            'invoice_main' => $invoiceMain,
            'invoice_return' => $invoiceReturn,
        ];
    }

    /**
     * Get top selling products within a date range.
     */
    public function getTopSellingProducts(?string $startDate = null, ?string $endDate = null): \Illuminate\Support\Collection
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subMonths(5)->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        return \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->whereBetween('sales.sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('sale_items.product_id, sale_items.product_name, sale_items.sku, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.line_total) as total_amount')
            ->groupBy('sale_items.product_id', 'sale_items.product_name', 'sale_items.sku')
            ->orderBy('total_quantity', 'desc')
            ->get();
    }

    /**
     * Get per-date or per-month breakdown for a single product within a date range.
     * Calculates peak day date, peak quantity, peak amount, total quantity, and total amount.
     */
    public function getTopSellingProductBreakdown(int $productId, ?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $diffDays = $start->diffInDays($end);
        $groupBy = $diffDays <= 31 ? 'date' : 'month';

        if ($groupBy === 'month') {
            $startNorm = $start->copy()->startOfMonth();
            $endNorm = $end->copy()->endOfMonth();
        } else {
            $startNorm = $start->copy();
            $endNorm = $end->copy();
        }

        $selectFormat = $groupBy === 'date' ? '%Y-%m-%d' : '%b %Y';

        // Aggregates per interval
        $amtRows = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->where('sale_items.product_id', $productId)
            ->whereBetween('sales.sale_date', [$startNorm->toDateString(), $endNorm->toDateString()])
            ->selectRaw("DATE_FORMAT(sales.sale_date, '$selectFormat') as label, SUM(sale_items.line_total) as amt")
            ->groupBy('label')
            ->pluck('amt', 'label')
            ->toArray();

        $qtyRows = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->where('sale_items.product_id', $productId)
            ->whereBetween('sales.sale_date', [$startNorm->toDateString(), $endNorm->toDateString()])
            ->selectRaw("DATE_FORMAT(sales.sale_date, '$selectFormat') as label, SUM(sale_items.quantity) as qty")
            ->groupBy('label')
            ->pluck('qty', 'label')
            ->toArray();

        // Single peak day in the selected date range
        $peakDayRow = \App\Models\SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereNull('sales.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->where('sale_items.product_id', $productId)
            ->whereBetween('sales.sale_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE(sales.sale_date) as d, SUM(sale_items.quantity) as qty, SUM(sale_items.line_total) as amt")
            ->groupBy('d')
            ->orderBy('qty', 'desc')
            ->first();

        // Build labels array
        $labels = [];
        $current = $startNorm->copy();
        while ($current <= $endNorm) {
            $lbl = $groupBy === 'date' ? $current->format('Y-m-d') : $current->format('M Y');
            if (!in_array($lbl, $labels)) {
                $labels[] = $lbl;
            }
            $groupBy === 'date' ? $current->addDay() : $current->addMonth();
        }

        $qtys = array_map(fn($l) => (float) ($qtyRows[$l] ?? 0), $labels);
        $amts = array_map(fn($l) => (float) ($amtRows[$l] ?? 0), $labels);

        $product = \App\Models\Product::find($productId);

        return [
            'product_id' => $productId,
            'product_name' => $product?->name ?? 'Product',
            'sku' => $product?->sku ?? '',
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'labels' => $labels,
            'quantities' => $qtys,
            'amounts' => $amts,
            'peak_date' => $peakDayRow?->d ? Carbon::parse($peakDayRow->d)->format('Y-m-d') : null,
            'peak_quantity' => $peakDayRow ? (float) $peakDayRow->qty : 0.0,
            'peak_amount' => $peakDayRow ? (float) $peakDayRow->amt : 0.0,
            'total_quantity' => array_sum($qtys),
            'total_amount' => array_sum($amts),
            'group_by' => $groupBy,
        ];
    }

    /**
     * Get aggregated chart data for Payments Sent and Received.
     */
    public function getPaymentChartData(?string $startDate = null, ?string $endDate = null): array
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subMonths(5)->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfMonth();

        $diffDays = $start->diffInDays($end);
        $groupBy = $diffDays <= 31 ? 'date' : 'month';
        $selectFormat = $groupBy === 'date' ? '%Y-%m-%d' : '%b %Y';

        $getAggregated = function ($modelClass, $dateColumn, $amountColumn) use ($start, $end, $selectFormat) {
            return $modelClass::whereBetween($dateColumn, [$start, $end])
                ->selectRaw("DATE_FORMAT($dateColumn, '$selectFormat') as label, SUM($amountColumn) as total")
                ->groupBy('label')
                ->pluck('total', 'label')
                ->toArray();
        };

        $paymentsReceived = $getAggregated(\App\Models\SalePayment::class, 'created_at', 'amount');
        $paymentsSent = $getAggregated(\App\Models\PurchasePayment::class, 'created_at', 'amount');

        $labels = [];
        $current = $start->copy();
        while ($current <= $end) {
            $label = $groupBy === 'date' ? $current->format('Y-m-d') : $current->format('M Y');
            if (!in_array($label, $labels)) {
                $labels[] = $label;
            }
            $groupBy === 'date' ? $current->addDay() : $current->addMonth();
        }

        $formatData = function ($data) use ($labels) {
            return array_map(fn($label) => (float) ($data[$label] ?? 0), $labels);
        };

        // Build refs/invoice arrays aligned with labels for payments
        $refsMain = [];
        $refsReturn = [];
        foreach ($labels as $label) {
            if ($groupBy === 'date') {
                $receivedPayments = \App\Models\SalePayment::whereDate('created_at', $label)->with('sale')->get();
                $sentPayments = \App\Models\PurchasePayment::whereDate('created_at', $label)->with('purchase')->get();
            } else {
                try {
                    $dt = Carbon::createFromFormat('M Y', $label);
                    $receivedPayments = \App\Models\SalePayment::whereYear('created_at', $dt->year)->whereMonth('created_at', $dt->month)->with('sale')->get();
                    $sentPayments = \App\Models\PurchasePayment::whereYear('created_at', $dt->year)->whereMonth('created_at', $dt->month)->with('purchase')->get();
                } catch (\Exception $e) {
                    $receivedPayments = collect();
                    $sentPayments = collect();
                }
            }

            $receivedRefs = $receivedPayments->map(fn($p) => $p->sale?->invoice_no)->filter()->unique()->values()->take(5)->toArray();
            $sentRefs = $sentPayments->map(fn($p) => $p->purchase?->invoice_no)->filter()->unique()->values()->take(5)->toArray();

            $refsMain[] = count($sentRefs) ? implode(', ', $sentRefs) : '';
            $refsReturn[] = count($receivedRefs) ? implode(', ', $receivedRefs) : '';
        }

        return [
            'labels' => $labels,
            'payments_received' => $formatData($paymentsReceived),
            'payments_sent' => $formatData($paymentsSent),
            'invoice_main' => $refsMain,
            'invoice_return' => $refsReturn,
        ];
    }
}
