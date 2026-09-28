<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerRoute;
use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StoreCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DashboardService;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected PdfService $pdfService,
        protected DashboardService $dashboardService
    ) {
    }

    /**
     * Display the unified Generated Reports center.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'sales');
        $validTabs = ['sales', 'purchases', 'dashboard'];
        if (!in_array($tab, $validTabs, true)) {
            $tab = 'sales';
        }

        $generalSetting = GeneralSetting::first();
        $perPage = $generalSetting?->records_per_page ?? 20;

        $data = [
            'activeTab' => $tab,
            'generalSetting' => $generalSetting,
        ];

        if ($tab === 'sales') {
            $query = $this->buildSalesQuery($request);

            // Calculate KPIs before pagination
            $kpis = [
                'total_count' => (clone $query)->count(),
                'total_amount' => (clone $query)->sum('receivable_amount'),
                'total_paid' => (clone $query)->sum('paid_amount'),
                'total_discount' => (clone $query)->sum('discount'),
                'total_due' => (clone $query)->get()->sum(fn ($s) => max(0, (float) $s->receivable_amount - (float) $s->paid_amount)),
            ];

            $sales = $query->paginate($perPage)->withQueryString();

            // Dropdown filter data
            $customers = Customer::select('id', 'name', 'phone')->orderBy('name')->get();
            $salespersons = User::ofRole('salesperson')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            if ($salespersons->isEmpty()) {
                $salespersons = User::select('id', 'name')->orderBy('name')->get();
            }
            $routes = CustomerRoute::select('id', 'name', 'from_location', 'to_location')
                ->active()
                ->orderBy('name')
                ->orderBy('from_location')
                ->get();
            $storeCategories = StoreCategory::select('id', 'name')->orderBy('name')->get();

            $data = array_merge($data, [
                'sales' => $sales,
                'kpis' => $kpis,
                'customers' => $customers,
                'salespersons' => $salespersons,
                'routes' => $routes,
                'storeCategories' => $storeCategories,
            ]);
        } elseif ($tab === 'purchases') {
            $query = $this->buildPurchasesQuery($request);

            // Calculate KPIs before pagination
            $kpis = [
                'total_count' => (clone $query)->count(),
                'total_amount' => (clone $query)->sum('payable_amount'),
                'total_paid' => (clone $query)->sum('paid_amount'),
                'total_discount' => (clone $query)->sum('discount'),
                'total_due' => (clone $query)->get()->sum(fn ($p) => max(0, (float) $p->payable_amount - (float) $p->paid_amount)),
            ];

            $purchases = $query->paginate($perPage)->withQueryString();

            // Dropdown filter data
            $suppliers = Supplier::select('id', 'name', 'phone')->orderBy('name')->get();

            $data = array_merge($data, [
                'purchases' => $purchases,
                'kpis' => $kpis,
                'suppliers' => $suppliers,
            ]);
        } elseif ($tab === 'dashboard') {
            $analytics = $this->buildDashboardAnalytics($request);
            $categories = Category::select('id', 'name')->orderBy('name')->get();
            $dateFrom = $analytics['date_from'];
            $dateTo = $analytics['date_to'];

            $topSellingProducts = $this->dashboardService->getTopSellingProducts($dateFrom, $dateTo);
            $categoryAnalytics = $this->dashboardService->getProductsPerCategory($dateFrom, $dateTo, 100);
            $allProducts = \App\Models\Product::select('id', 'name', 'sku')->orderBy('name')->get();

            $data = array_merge($data, [
                'analytics' => $analytics,
                'categories' => $categories,
                'allProducts' => $allProducts,
                'topSellingProducts' => $topSellingProducts,
                'categoryAnalytics' => $categoryAnalytics,
            ]);
        }

        return view('reports.generated', $data);
    }

    /**
     * Build the filtered Sales query.
     */
    protected function buildSalesQuery(Request $request): Builder
    {
        $query = Sale::notCancelled()
            ->with(['customer', 'salesperson', 'user'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id');

        // Check if salesperson role restriction applies
        $user = auth()->user();
        if ($user && $user->hasRole('salesperson')) {
            $query->where('salesperson_id', $user->id);
        } elseif ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->input('salesperson_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('route_id')) {
            $query->whereHas('customer', function ($cq) use ($request) {
                $cq->where('route_id', $request->input('route_id'));
            });
        }

        if ($request->filled('store_category_id')) {
            $scId = $request->input('store_category_id');
            $query->where(function ($q) use ($scId) {
                $q->where('store_category_id', $scId)
                  ->orWhereHas('customer', fn ($cq) => $cq->where('store_category_id', $scId));
            });
        }

        if ($request->filled('payment_status')) {
            $pStatus = $request->input('payment_status');
            if ($pStatus === 'paid') {
                $query->whereRaw('paid_amount >= receivable_amount');
            } elseif ($pStatus === 'due') {
                $query->whereRaw('paid_amount = 0');
            } elseif ($pStatus === 'partial') {
                $query->whereRaw('paid_amount > 0 AND paid_amount < receivable_amount');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->input('date_to'));
        }

        return $query;
    }

    /**
     * Build the filtered Purchases query.
     */
    protected function buildPurchasesQuery(Request $request): Builder
    {
        $query = Purchase::query()
            ->with(['supplier'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status')) {
            $pStatus = $request->input('payment_status');
            if ($pStatus === 'paid') {
                $query->whereRaw('paid_amount >= payable_amount');
            } elseif ($pStatus === 'due') {
                $query->whereRaw('paid_amount = 0');
            } elseif ($pStatus === 'partial') {
                $query->whereRaw('paid_amount > 0 AND paid_amount < payable_amount');
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->input('date_to'));
        }

        return $query;
    }

    /**
     * Build aggregated business performance analytics for Dashboard tab.
     */
    protected function buildDashboardAnalytics(Request $request): array
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());

        // Sales in range
        $salesQuery = Sale::notCancelled()
            ->whereDate('sale_date', '>=', $dateFrom)
            ->whereDate('sale_date', '<=', $dateTo);

        $totalSales = (float) (clone $salesQuery)->sum('receivable_amount');
        $totalSalesPaid = (float) (clone $salesQuery)->sum('paid_amount');
        $salesCount = (int) (clone $salesQuery)->count();

        // Returns in range
        $returnsQuery = \App\Models\SaleReturn::where('status', '!=', \App\Models\SaleReturn::STATUS_REJECTED)
            ->whereDate('return_date', '>=', $dateFrom)
            ->whereDate('return_date', '<=', $dateTo);
        $totalReturns = (float) (clone $returnsQuery)->sum('payable_amount');
        $netSales = max(0, $totalSales - $totalReturns);

        // Purchases in range
        $purchasesQuery = Purchase::query()
            ->whereDate('purchase_date', '>=', $dateFrom)
            ->whereDate('purchase_date', '<=', $dateTo);

        $totalPurchases = (float) (clone $purchasesQuery)->sum('payable_amount');
        $totalPurchasesPaid = (float) (clone $purchasesQuery)->sum('paid_amount');
        $purchasesCount = (int) (clone $purchasesQuery)->count();

        $grossProfit = $netSales - $totalPurchases;
        $profitMargin = $netSales > 0 ? round(($grossProfit / $netSales) * 100, 1) : 0;

        // Top Selling Products in Range
        $topProducts = \App\Models\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereNull('sales.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->whereDate('sales.sale_date', '>=', $dateFrom)
            ->whereDate('sales.sale_date', '<=', $dateTo)
            ->selectRaw('products.id, products.name, products.sku, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.line_total) as total_amount')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get();

        // Sales breakdown per Category
        $categoryBreakdown = \App\Models\SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereNull('sales.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNotIn('sales.status', ['cancelled', 'rejected'])
            ->where('sales.delivery_status', \App\Models\Sale::DELIVERY_DELIVERED)
            ->whereDate('sales.sale_date', '>=', $dateFrom)
            ->whereDate('sales.sale_date', '<=', $dateTo)
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category_name, SUM(sale_items.quantity) as total_quantity, SUM(sale_items.line_total) as total_amount")
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_amount')
            ->get();

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'total_sales' => $totalSales,
            'total_sales_paid' => $totalSalesPaid,
            'sales_count' => $salesCount,
            'sales_due' => max(0, $totalSales - $totalSalesPaid),
            'total_purchases' => $totalPurchases,
            'total_purchases_paid' => $totalPurchasesPaid,
            'purchases_count' => $purchasesCount,
            'purchases_due' => max(0, $totalPurchases - $totalPurchasesPaid),
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'top_products' => $topProducts,
            'category_breakdown' => $categoryBreakdown,
        ];
    }

    /**
     * Export filtered sales report to CSV.
     */
    public function exportSalesCsv(Request $request): StreamedResponse
    {
        $sales = $this->buildSalesQuery($request)->get();
        $filename = 'sales-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Customer',
                'Phone',
                'Salesperson',
                'Subtotal',
                'Discount',
                'Receivable',
                'Received',
                'Due',
                'Status',
            ]);

            foreach ($sales as $s) {
                $due = max(0, (float) $s->receivable_amount - (float) $s->paid_amount);
                $status = $due <= 0 ? 'Paid' : ($s->paid_amount > 0 ? 'Partial' : 'Due');

                fputcsv($out, [
                    $s->invoice_no,
                    $s->sale_date?->format('Y-m-d') ?? '',
                    $s->customer?->name ?? 'Walk-in',
                    $s->customer?->phone ?? '',
                    $s->salesperson?->name ?? ($s->user?->name ?? 'N/A'),
                    number_format((float) $s->subtotal, 2, '.', ''),
                    number_format((float) $s->discount, 2, '.', ''),
                    number_format((float) $s->receivable_amount, 2, '.', ''),
                    number_format((float) $s->paid_amount, 2, '.', ''),
                    number_format($due, 2, '.', ''),
                    $status,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export filtered sales report to PDF.
     */
    public function exportSalesPdf(Request $request)
    {
        $sales = $this->buildSalesQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $kpis = [
            'total_count' => $sales->count(),
            'total_amount' => $sales->sum('receivable_amount'),
            'total_paid' => $sales->sum('paid_amount'),
            'total_due' => $sales->sum(fn ($s) => max(0, (float) $s->receivable_amount - (float) $s->paid_amount)),
        ];

        $data = [
            'sales' => $sales,
            'kpis' => $kpis,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfService->logoDataUri($generalSetting),
            'filters' => [
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'payment_status' => $request->input('payment_status'),
                'salesperson' => $request->filled('salesperson_id') ? User::find($request->input('salesperson_id'))?->name : null,
                'customer' => $request->filled('customer_id') ? Customer::find($request->input('customer_id'))?->name : null,
                'route' => $request->filled('route_id') ? CustomerRoute::find($request->input('route_id'))?->display_name : null,
                'store_category' => $request->filled('store_category_id') ? StoreCategory::find($request->input('store_category_id'))?->name : null,
            ],
        ];

        $filename = 'sales-report-' . now()->format('Y-m-d-His') . '.pdf';

        if ($request->has('print')) {
            return $this->pdfService->stream('reports.pdf.sales-report-pdf', $data, $filename, 'landscape');
        }

        return $this->pdfService->download('reports.pdf.sales-report-pdf', $data, $filename, 'landscape');
    }

    /**
     * Export filtered purchases report to CSV.
     */
    public function exportPurchasesCsv(Request $request): StreamedResponse
    {
        $purchases = $this->buildPurchasesQuery($request)->get();
        $filename = 'purchases-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($purchases) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Supplier',
                'Phone',
                'Status',
                'Subtotal',
                'Discount',
                'Payable',
                'Paid',
                'Due',
                'Payment Status',
            ]);

            foreach ($purchases as $p) {
                $due = max(0, (float) $p->payable_amount - (float) $p->paid_amount);
                $paymentStatus = $due <= 0 ? 'Paid' : ($p->paid_amount > 0 ? 'Partial' : 'Due');

                fputcsv($out, [
                    $p->invoice_no,
                    $p->purchase_date?->format('Y-m-d') ?? '',
                    $p->supplier?->name ?? 'N/A',
                    $p->supplier?->phone ?? '',
                    ucfirst($p->status ?? 'Ordered'),
                    number_format((float) $p->subtotal, 2, '.', ''),
                    number_format((float) $p->discount, 2, '.', ''),
                    number_format((float) $p->payable_amount, 2, '.', ''),
                    number_format((float) $p->paid_amount, 2, '.', ''),
                    number_format($due, 2, '.', ''),
                    $paymentStatus,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export filtered purchases report to PDF.
     */
    public function exportPurchasesPdf(Request $request)
    {
        $purchases = $this->buildPurchasesQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $kpis = [
            'total_count' => $purchases->count(),
            'total_amount' => $purchases->sum('payable_amount'),
            'total_paid' => $purchases->sum('paid_amount'),
            'total_due' => $purchases->sum(fn ($p) => max(0, (float) $p->payable_amount - (float) $p->paid_amount)),
        ];

        $data = [
            'purchases' => $purchases,
            'kpis' => $kpis,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfService->logoDataUri($generalSetting),
            'filters' => [
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'status' => $request->input('status'),
                'payment_status' => $request->input('payment_status'),
                'supplier' => $request->filled('supplier_id') ? Supplier::find($request->input('supplier_id'))?->name : null,
            ],
        ];

        $filename = 'purchases-report-' . now()->format('Y-m-d-His') . '.pdf';

        if ($request->has('print')) {
            return $this->pdfService->stream('reports.pdf.purchases-report-pdf', $data, $filename, 'landscape');
        }

        return $this->pdfService->download('reports.pdf.purchases-report-pdf', $data, $filename, 'landscape');
    }

    /**
     * Export executive dashboard analytics report to PDF.
     */
    public function exportDashboardPdf(Request $request)
    {
        $analytics = $this->buildDashboardAnalytics($request);
        $generalSetting = GeneralSetting::first();

        $data = [
            'analytics' => $analytics,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfService->logoDataUri($generalSetting),
        ];

        $filename = 'dashboard-executive-report-' . now()->format('Y-m-d-His') . '.pdf';

        if ($request->has('print')) {
            return $this->pdfService->stream('reports.pdf.dashboard-report-pdf', $data, $filename, 'portrait');
        }

        return $this->pdfService->download('reports.pdf.dashboard-report-pdf', $data, $filename, 'portrait');
    }
}
