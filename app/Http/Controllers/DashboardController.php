<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

use Illuminate\Http\JsonResponse;

use App\Models\GeneralSetting;
use App\Models\Sale;
use App\Services\PdfService;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected \App\Services\StaffPerformanceService $performanceService
    ) {
    }

    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $summary = $this->dashboardService->getSummaryStats();
        $transactions = $this->dashboardService->getTransactionStats();
        $recentSales = $this->dashboardService->getRecentSales();
        $settings = GeneralSetting::first();
        
        // Check if user is salesperson
        $isSalesperson = auth()->check() && auth()->user()->hasRole('salesperson');
        
        if ($isSalesperson) {
            $user = auth()->user();

            // Get today's sales for this salesperson
            $todaySales = Sale::notCancelled()
                ->whereDate('created_at', today())
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('salesperson_id', $user->id);
                })
                ->selectRaw('count(*) as count, COALESCE(SUM(receivable_amount), 0) as total_amount')
                ->first();

            // Fetch overall team quota performance & individual salesperson contribution
            $perfData = $this->performanceService->getDashboardPerformance();
            $userPerf = $perfData['staff']->firstWhere('user.id', $user->id);
            $teamRank = $perfData['staff']->search(fn ($p) => $p->user->id === $user->id);
            $teamRank = $teamRank === false ? $perfData['staff']->count() : $teamRank + 1;

            if (!$userPerf) {
                $actualSales = $this->performanceService->calculateActualSales($user, $perfData['startDate'], $perfData['endDate']);
                $totalTeamSales = $perfData['totalSales'];
                $contributionPct = $totalTeamSales > 0 ? round(($actualSales / $totalTeamSales) * 100, 1) : 0.0;
                $invoicesCount = Sale::notCancelled()
                    ->whereBetween('sale_date', [$perfData['startDate'], $perfData['endDate']])
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhere('salesperson_id', $user->id);
                    })->count();

                $userPerf = (object) [
                    'user' => $user,
                    'actual_sales' => $actualSales,
                    'invoices_count' => $invoicesCount,
                    'contribution_pct' => $contributionPct,
                    'quota_share_pct' => $perfData['targetAmount'] > 0 ? round(($actualSales / $perfData['targetAmount']) * 100, 1) : 0.0,
                    'commission_earned' => $perfData['isAchieved'] ? $perfData['commissionPerStaff'] : 0.0,
                    'commission_status' => $perfData['isAchieved'] ? 'Awarded' : 'Pending Quota',
                ];
            }
                
            return view('salesperson.dashboard', [
                'summary' => $summary,
                'todaySales' => $todaySales,
                'settings' => $settings,
                'overallQuota' => $perfData['overallQuota'],
                'targetAmount' => $perfData['targetAmount'],
                'totalTeamSales' => $perfData['totalSales'],
                'isAchieved' => $perfData['isAchieved'],
                'overallProgress' => $perfData['progress'],
                'remainingAmount' => $perfData['remainingAmount'],
                'commissionPerStaff' => $perfData['commissionPerStaff'],
                'startDate' => $perfData['startDate'],
                'endDate' => $perfData['endDate'],
                'userPerf' => $userPerf,
                'teamRank' => $teamRank,
                'totalStaff' => $perfData['staff']->count(),
            ]);
        }

        $stockAlerts = $this->dashboardService->getStockAlerts(50);
        $defaultStart = now()->startOfMonth()->format('Y-m-d');
        $defaultEnd   = now()->format('Y-m-d');
        $categoryAnalytics = $this->dashboardService->getProductsPerCategory($defaultStart, $defaultEnd, 100);
        $allProducts = \Illuminate\Support\Facades\Cache::remember('dashboard_all_products_dropdown', 120, function () {
            return \App\Models\Product::select('id', 'name', 'sku')->orderBy('name')->get();
        });

        return view('dashboard', [
            'summary' => $summary,
            'transactions' => $transactions,
            'recentSales' => $recentSales,
            'settings' => $settings,
            'stockAlerts' => $stockAlerts,
            'categoryAnalytics' => $categoryAnalytics,
            'allProducts' => $allProducts,
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'summary' => $this->dashboardService->getSummaryStats(),
            'transactions' => $this->dashboardService->getTransactionStats(),
        ]);
    }

    /**
     * Get data for dashboard charts.
     */
    public function chartData(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->dashboardService->getChartData($startDate, $endDate);

        return response()->json($data);
    }

    /**
     * Get data for top selling products.
     */
    public function topSellingData(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->dashboardService->getTopSellingProducts($startDate, $endDate);

        return response()->json($data);
    }

    /**
     * Get per-date or per-month breakdown for a single product within a date range.
     */
    public function topSellingBreakdown(Request $request): JsonResponse
    {
        $productId = (int) $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$productId) {
            return response()->json(['error' => 'product_id required'], 400);
        }

        $data = $this->dashboardService->getTopSellingProductBreakdown($productId, $startDate, $endDate);

        return response()->json($data);
    }

    /**
     * Export top selling products report as PDF.
     */
    public function exportTopSellingPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $ids = collect(explode(',', $request->input('ids', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->all();

        $products = $this->dashboardService->getTopSellingProducts($startDate, $endDate);

        if (!empty($ids)) {
            $products = $products->filter(fn ($product) => in_array($product->product_id, $ids))->values();
        }

        $selectedProduct = $products->count() === 1 ? $products->first() : null;
        $breakdown = null;

        if ($selectedProduct) {
            $breakdown = $this->dashboardService->getTopSellingProductBreakdown($selectedProduct->product_id, $startDate, $endDate);
        }

        $pdfService = app(PdfService::class);
        $filename = 'top-selling-products-' . now()->format('YmdHis') . '.pdf';

        $viewData = [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'breakdown' => $breakdown,
            'start' => $startDate,
            'end' => $endDate,
            'settings' => GeneralSetting::first(),
        ];

        if ($request->has('print')) {
            return $pdfService->stream('reports.dashboard-top-selling-pdf', $viewData, $filename, 'portrait');
        }

        return $pdfService->download('reports.dashboard-top-selling-pdf', $viewData, $filename, 'portrait');
    }

    /**
     * Export Product Sales Performance & Peak Date Tracker report as PDF.
     */
    public function exportProductSalesPerformancePdf(Request $request)
    {
        $productId = (int) $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$productId) {
            return redirect()->back()->with('error', 'Please select a product to print performance report.');
        }

        $breakdown = $this->dashboardService->getTopSellingProductBreakdown($productId, $startDate, $endDate);
        $product = \App\Models\Product::with(['category', 'brand'])->find($productId);

        $pdfService = app(PdfService::class);
        $settings = GeneralSetting::first();
        $logoSrc = $pdfService->logoDataUri($settings);
        $filename = 'product-sales-performance-' . ($product?->sku ?: $productId) . '-' . now()->format('YmdHis') . '.pdf';

        $viewData = [
            'product' => $product,
            'breakdown' => $breakdown,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'settings' => $settings,
            'generalSetting' => $settings,
            'logoSrc' => $logoSrc,
        ];

        if ($request->has('print')) {
            return $pdfService->stream('reports.product-sales-performance-pdf', $viewData, $filename, 'landscape');
        }

        return $pdfService->download('reports.product-sales-performance-pdf', $viewData, $filename, 'landscape');
    }

    /**
     * Get data for payments chart.
     */
    public function paymentChartData(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->dashboardService->getPaymentChartData($startDate, $endDate);

        return response()->json($data);
    }
    /**
     * Export dashboard report as PDF and stream to browser.
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $chart = $request->input('chart', 'chart1');

        if ($chart === 'chart3') {
            $data = $this->dashboardService->getPaymentChartData($startDate, $endDate);
        } else {
            $data = $this->dashboardService->getChartData($startDate, $endDate);
        }

        $pdfService = app(PdfService::class);
        $filename = Str::slug(($chart ?? 'dashboard').'-report-'.now()->format('Ymd')) . '.pdf';

        $viewData = [
            'data' => $data,
            'chart' => $chart,
            'start' => $startDate,
            'end' => $endDate,
            'settings' => GeneralSetting::first(),
        ];

        return $pdfService->stream('reports.dashboard-report-pdf', $viewData, $filename, 'portrait');
    }

    /**
     * Get JSON data for category performance by date.
     */
    public function categoryPerformanceData(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->dashboardService->getProductsPerCategory($startDate, $endDate, 100);

        return response()->json($data);
    }

    /**
     * Get timeline breakdown for selected category.
     */
    public function categoryBreakdownData(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id', 'ALL');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $data = $this->dashboardService->getCategoryBreakdown($categoryId, $startDate, $endDate);

        return response()->json($data);
    }

    /**
     * Export category performance report as PDF.
     */
    public function exportCategoryPerformancePdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $analytics = $this->dashboardService->getProductsPerCategory($startDate, $endDate, 100);

        // Filter to selected category IDs if provided
        $categoryIds = $request->input('category_ids') ?? $request->input('category_id');
        if ($categoryIds && $categoryIds !== 'ALL') {
            $ids = collect(explode(',', $categoryIds))->map(fn($id) => trim($id))->filter(fn($id) => $id !== '')->all();
            if (!empty($ids)) {
                $filteredItems = array_values(array_filter(
                    $analytics['all_items'] ?? $analytics['items'] ?? [],
                    fn($item) => in_array((string) $item['id'], $ids, true) || in_array($item['id'], array_map('intval', $ids), true)
                ));
                $analytics['items'] = $filteredItems;
                $analytics['all_items'] = $filteredItems;

                // Recalculate metrics for selected categories only
                $analytics['total_period_sales'] = array_sum(array_column($filteredItems, 'total_sales_amount'));
                $analytics['total_period_qty'] = array_sum(array_column($filteredItems, 'total_sold_qty'));
                $analytics['total_categories'] = count($filteredItems);
                $analytics['total_products'] = array_sum(array_map(fn($item) => count($item['products'] ?? []), $filteredItems));

                // Recalculate percentages relative to selected categories
                $totalFilteredSales = $analytics['total_period_sales'];
                foreach ($analytics['items'] as &$item) {
                    $item['percentage'] = $totalFilteredSales > 0
                        ? round(($item['total_sales_amount'] / $totalFilteredSales) * 100, 1)
                        : 0;
                }
                unset($item);
                $analytics['all_items'] = $analytics['items'];
            }
        }

        $pdfService = app(PdfService::class);
        $filename = 'category-performance-report-' . now()->format('YmdHis') . '.pdf';

        $viewData = [
            'analytics' => $analytics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'settings' => GeneralSetting::first(),
            'logoDataUri' => $pdfService->logoDataUri(),
        ];

        return $pdfService->stream('reports.category-performance-pdf', $viewData, $filename, 'portrait');
    }
}
