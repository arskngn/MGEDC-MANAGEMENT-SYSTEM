<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\SalePayment;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Services\PdfService;

class PageController extends Controller
{
    public function salesAll()
    {
        return view('pages.empty', ['title' => 'All Sales']);
    }

    public function salesReturn(Request $request)
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $customerId = $request->input('customer_id');
        $status = $request->input('status');

        $user = $request->user();
        $isSalesperson = $user && $user->hasRole('salesperson');

        $salesReturns = \App\Models\SaleReturn::query()
            ->with(['sale:id,invoice_no,sale_date,created_at,customer_id,salesperson_id,receivable_amount,paid_amount', 'sale.customer', 'sale.saleReturns' => function ($q) {
                $q->orderBy('id', 'asc');
            }])
            ->whereHas('sale.customer')
            ->when($isSalesperson, function ($q) use ($user) {
                $q->whereHas('sale', function ($sq) use ($user) {
                    $sq->where('salesperson_id', $user->id);
                });
            })
            ->when($customerId, function ($q) use ($customerId) {
                $q->whereHas('sale', function ($sq) use ($customerId) {
                    $sq->where('customer_id', $customerId);
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('sale.customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $customersQuery = \App\Models\Customer::query()->active();
        if ($isSalesperson) {
            $customersQuery->whereHas('sales', function ($sq) use ($user) {
                $sq->where('salesperson_id', $user->id);
            });
        }
        $customers = $customersQuery->orderBy('name')->get(['id', 'name']);

        return view('sales.return-index', [
            'salesReturns' => $salesReturns,
            'customers' => $customers,
        ]);
    }

    public function customers()
    {
        return view('pages.empty', ['title' => 'Manage Customers']);
    }

    public function suppliers()
    {
        return view('pages.empty', ['title' => 'Manage Suppliers']);
    }

    public function staffAll()
    {
        return view('pages.empty', ['title' => 'All Staff']);
    }

    public function staffRoles()
    {
        return view('pages.empty', ['title' => 'Roles']);
    }

    public function transfers()
    {
        return view('pages.empty', ['title' => 'Transfers']);
    }

    public function expenseTypes()
    {
        return view('pages.empty', ['title' => 'Expense Types']);
    }

    public function expenses()
    {
        return view('pages.empty', ['title' => 'Expenses']);
    }

    public function reportSupplierPayments(Request $request)
    {
        // Redirect to the new supplier payments page
        return redirect()->route('supplier-payments.index', $request->query());
    }

    public function reportCustomerPayments(Request $request)
    {
        // Redirect to the new customer payments page
        return redirect()->route('customer-payments.index', $request->query());
    }

    public function reportStock(Request $request)
    {
        // Eager load unit and batches count to eliminate N+1 queries
        $products = \App\Models\Product::query()
            ->with('unit')
            ->withCount('batches')
            ->orderBy('name')
            ->get();

        $stockData = $products->map(function ($product) {
            $stock = (float) ($product->current_stock ?? 0);
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'stock_quantity' => $stock,
                'alert_quantity' => $product->alert_quantity,
                'unit' => $product->unit?->short_name ?? 'Unit',
                'is_low_stock' => $stock <= $product->alert_quantity,
                'batches_count' => (int) $product->batches_count,
            ];
        });

        $productsList = $products->map->only(['id', 'name', 'sku']);

        return view('pages.stock-report', [
            'title' => 'Stock Report',
            'products' => $productsList,
            'stockData' => $stockData,
        ]);
    }

    private function getProductStock(int $productId): float
    {
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return 0;
        }
        return (float) ($product->current_stock ?? 0);
    }

    public function exportStock(Request $request)
    {
        $format = $request->string('format')->value() ?: 'pdf';
        
        // Check permission based on format
        if ($format === 'pdf' && !$request->user()->hasPermission('Download Stock Report PDF')) {
            abort(403, 'Unauthorized action.');
        }
        if ($format === 'csv' && !$request->user()->hasPermission('Download Stock Report CSV')) {
            abort(403, 'Unauthorized action.');
        }

        $products = \App\Models\Product::query()
            ->with(['unit', 'category', 'brand'])
            ->orderBy('name')
            ->get();

        $stockData = $products->map(function ($product) {
            $stock = (float) ($product->current_stock ?? 0);
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'category_name' => $product->category?->name ?? '-',
                'brand_name' => $product->brand?->name ?? '-',
                'stock_quantity' => $stock,
                'alert_quantity' => $product->alert_quantity,
                'unit' => $product->unit?->short_name ?? 'Unit',
                'is_low_stock' => $stock <= $product->alert_quantity,
            ];
        });

        if ($format === 'csv') {
            return $this->exportStockCsv($stockData);
        }

        return $this->exportStockPdf($stockData);
    }

    private function exportStockCsv($stockData)
    {
        $filename = 'stock-report-' . date('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($stockData) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['S.N.', 'Product Name', 'SKU', 'Category', 'Brand', 'Stock', 'Unit']);
            foreach ($stockData as $index => $item) {
                fputcsv($out, [
                    $index + 1,
                    $item['product_name'],
                    $item['sku'],
                    $item['category_name'] ?? '-',
                    $item['brand_name'] ?? '-',
                    $item['stock_quantity'],
                    $item['unit']
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }


    private function exportStockPdf($stockData)
    {
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'stockData' => $stockData,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'stock-report-' . date('Y-m-d-H-i-s') . '.pdf';

        return $pdfService->download('pages.stock-report-pdf', $data, $filename, 'landscape');
    }



    public function reportDataEntryPurchases()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Purchase::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Purchase::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Purchases',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'purchase',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    private function applyDataEntryFilters($query)
    {
        // Filter by action type (CREATED, UPDATED, DELETED)
        if (request('type')) {
            $query->where('action', strtolower(request('type')));
        }

        // Filter by user
        if (request('by')) {
            $query->where('user_id', request('by'));
        }

        // Filter by date range
        if (request('date_from')) {
            $query->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $query->whereDate('created_at', '<=', request('date_to'));
        }

        return $query;
    }

    public function reportDataEntryPurchaseReturn()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\PurchaseReturn::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\PurchaseReturn::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Purchase Return',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'purchase-return',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntrySales()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Sale::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Sale::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Sales',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'sale',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntrySaleReturn()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\SaleReturn::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\SaleReturn::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Sale Return',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'sale-return',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntryProducts()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Product::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Product::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Products',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'product',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntryCustomers()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Customer::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Customer::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Customers',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'customer',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntryCustomerPayments()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', SalePayment::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = SalePayment::with('sale.customer')->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Customer Payments',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'customer-payment',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntrySuppliers()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Supplier::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Supplier::withTrashed()->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Suppliers',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'supplier',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntrySupplierPayments()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', PurchasePayment::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = PurchasePayment::with('purchase.supplier')->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Supplier Payments',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'supplier-payment',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntryAdjustments()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Adjustment::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Adjustment::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Adjustments',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'adjustment',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function reportDataEntryExpenses()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $query = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Expense::class)
            ->with('user');

        // Apply filters
        $query = $this->applyDataEntryFilters($query);

        // Apply sorting
        $sort = request('sort', 'desc');
        $query = $query->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc')
                      ->orderBy('id', $sort === 'asc' ? 'asc' : 'desc');

        $activities = $query->paginate($perPage)->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Expense::with('expenseType')->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Expenses',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'expense',
            'users' => \App\Models\User::orderBy('name')->get()
        ]);
    }

    public function extraApplication()
    {
        $generalSetting = GeneralSetting::first();
        $data = [
            'title' => 'Application Information',
            'app_name' => $generalSetting->site_title ?? 'MGEDC',
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'timezone' => $generalSetting->timezone ?? 'UTC',
        ];

        return view('pages.extra.application', $data);
    }

    public function extraServer()
    {
        // Detect server software
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? null;
        if (! $server_software) {
            // Try to detect from common server software headers
            $server_software = $_SERVER['HTTP_USER_AGENT'] ?? 'Not Detected';
        }

        // PHP SAPI (Server API) - shows if CLI, FPM, Apache, etc.
        $php_sapi = php_sapi_name();

        // Operating System
        $os = php_uname('s');

        // Server Address/IP
        $server_ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()) ?? 'Not Detected';

        // HTTP Protocol
        $server_protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'Not Detected';

        // HTTP Host
        $http_host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'Not Detected');

        // Server Port
        $server_port = $_SERVER['SERVER_PORT'] ?? 'Not Detected';

        $data = [
            'title' => 'Server Information',
            'php_version' => phpversion(),
            'php_sapi' => $php_sapi,
            'server_software' => $server_software,
            'operating_system' => $os,
            'server_ip' => $server_ip,
            'server_protocol' => $server_protocol,
            'http_host' => $http_host,
            'server_port' => $server_port,
        ];

        return view('pages.extra.server', $data);
    }

    public function extraCache()
    {
        return view('pages.extra.cache', ['title' => 'Clear System Cache']);
    }

    public function clearCache(Request $request)
    {
        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize:clear');

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'All caches cleared successfully!']);
            }

            return back()->with('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error clearing cache: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Error clearing cache: '.$e->getMessage());
        }
    }

    public function extraUpdate()
    {
        return view('pages.extra.update', ['title' => 'System Updates']);
    }

}
