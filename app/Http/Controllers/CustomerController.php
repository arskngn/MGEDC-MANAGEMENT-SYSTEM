<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\StoreCategory;
use App\Services\CustomerService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    use \App\Http\Controllers\Concerns\ResolvesCsvUploadPath;

    public function __construct(protected CustomerService $customerService)
    {
    }

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;

        $customers = $this->filteredCustomersQuery($request)
            ->with(['storeCategory', 'route'])
            ->paginate($perPage)
            ->withQueryString();

        $categories = Cache::remember('store_categories_names', 86400, fn () => StoreCategory::pluck('name')->toArray());
        $routes = \App\Models\CustomerRoute::active()->orderBy('from_location')->get();

        return view('customers.index', [
            'customers' => $customers,
            'categories' => $categories,
            'routes' => $routes,
        ]);

    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->createCustomer($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $customers = $this->filteredCustomersQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'customers' => $customers,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'customers-'.time().'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('customers.pdf.all', $data, $filename, 'landscape');
        }

        return $pdfService->download('customers.pdf.all', $data, $filename, 'landscape');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $customers = $this->filteredCustomersQuery($request)->get();

        return $this->customerService->exportToCsv($customers);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'customer.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['name', 'email', 'mobile', 'address']);
            fputcsv($out, ['Sample Store / Customer Name', 'customer@example.com', '', 'Sample Address']);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,text/plain,text/csv'],
        ]);

        $file = $request->file('csv_file');
        $path = $this->csvUploadPath($file);

        $result = $this->customerService->importFromCsv($path);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if ($result['skipped'] > 0) {
            $msg = 'Imported ' . $result['imported'] . ' customers. Skipped ' . $result['skipped'] . ' rows.';
            if ($result['firstError']) {
                $msg .= ' ' . $result['firstError'];
            }
            return redirect()->route('customers.index')->with('error', $msg);
        }

        return redirect()->route('customers.index')->with('success', 'Customers imported successfully.');
    }

    private function filteredCustomersQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $storeCategory = $request->string('store_category')->trim()->value();
        $routeId = $request->string('route_id')->trim()->value();
        $status = $request->string('status')->trim()->value();
        $ids = $request->input('ids');

        // Calculate true unpaid sales receivable (taking returns into account)
        $receivableSub = DB::table('sales')
            ->leftJoinSub(
                DB::table('sale_returns')
                    ->whereNull('deleted_at')
                    ->select('sale_id', DB::raw('SUM(payable_amount) as total_return_payable'))
                    ->groupBy('sale_id'),
                'sr_sum',
                'sr_sum.sale_id',
                '=',
                'sales.id'
            )
            ->whereNull('sales.deleted_at')
            ->select(
                'sales.customer_id',
                DB::raw('SUM(GREATEST(0, (sales.receivable_amount - COALESCE(sr_sum.total_return_payable, 0)) - sales.paid_amount)) as receivable_total')
            )
            ->groupBy('sales.customer_id');

        // Calculate cash refund payables owed to customer from returns
        $payableSub = DB::table('sale_returns')
            ->join('sales', 'sales.id', '=', 'sale_returns.sale_id')
            ->whereNull('sale_returns.deleted_at')
            ->whereNull('sales.deleted_at')
            ->select(
                'sales.customer_id',
                DB::raw('SUM(GREATEST(0, (sale_returns.payable_amount - GREATEST(0, sales.receivable_amount - sales.paid_amount)) - sale_returns.paid_amount)) as payable_total')
            )
            ->groupBy('sales.customer_id');

        return Customer::query()
            ->leftJoinSub($receivableSub, 'r', function ($join) {
                $join->on('r.customer_id', '=', 'customers.id');
            })
            ->leftJoinSub($payableSub, 'p', function ($join) {
                $join->on('p.customer_id', '=', 'customers.id');
            })
            ->select(
                'customers.*',
                DB::raw('COALESCE(r.receivable_total, 0) as receivable_total'),
                DB::raw('COALESCE(p.payable_total, 0) as payable_total')
            )

            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('customers.id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('customers.name', 'like', '%'.$search.'%')
                        ->orWhere('customers.phone', 'like', '%'.$search.'%')
                        ->orWhere('customers.email', 'like', '%'.$search.'%')
                        ->orWhere('customers.address', 'like', '%'.$search.'%');
                });
            })
            ->when($storeCategory !== '', function ($q) use ($storeCategory) {
                $storeCategoryModel = StoreCategory::where('name', $storeCategory)->first();

                if ($storeCategoryModel) {
                    $q->where('customers.store_category_id', $storeCategoryModel->id);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->when($routeId !== '', function ($q) use ($routeId) {
                $q->where('customers.route_id', $routeId);
            })
            ->when($status !== '', function ($q) use ($status) {
                $q->where(['customers.status' => $status]);
            })
            ->orderByRaw('customers.id desc');
    }

    private function csvRowIsEmpty(?array $row): bool
    {
        if (! is_array($row) || $row === []) {
            return true;
        }

        foreach ($row as $cell) {
            if (is_string($cell) && trim($cell) !== '') {
                return false;
            }
        }

        return true;
    }


}

