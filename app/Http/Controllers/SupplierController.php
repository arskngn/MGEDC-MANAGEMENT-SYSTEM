<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\GeneralSetting;
use App\Models\Supplier;
use App\Services\SupplierService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    use ResolvesCsvUploadPath;

    public function __construct(protected SupplierService $supplierService)
    {
    }

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;

        $suppliers = $this->filteredSuppliersQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function store(StoreSupplierRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->supplierService->createSupplier($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): \Illuminate\Http\RedirectResponse
    {
        $this->supplierService->updateSupplier($supplier, $request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $suppliers = $this->filteredSuppliersQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'suppliers' => $suppliers,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'suppliers-'.time().'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('suppliers.pdf.all', $data, $filename, 'landscape');
        }

        return $pdfService->download('suppliers.pdf.all', $data, $filename, 'landscape');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $suppliers = $this->filteredSuppliersQuery($request)->get();

        return $this->supplierService->exportToCsv($suppliers);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'supplier.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['name', 'email', 'mobile', 'company_name', 'address']);
            fputcsv($out, ['Sample Supplier', 'supplier@example.com', '+14155551234', 'Sample Company', 'Sample Address']);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,text/plain,text/csv'],
        ]);

        $file = $request->file('csv_file');
        $path = $this->csvUploadPath($file);

        $result = $this->supplierService->importFromCsv($path);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if ($result['skipped'] > 0) {
            $msg = 'Imported ' . $result['imported'] . ' suppliers. Skipped ' . $result['skipped'] . ' rows.';
            if ($result['firstError']) {
                $msg .= ' ' . $result['firstError'];
            }
            return redirect()->route('suppliers.index')->with('error', $msg);
        }

        return redirect()->route('suppliers.index')->with('success', 'Suppliers imported successfully.');
    }

    private function filteredSuppliersQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $ids = $request->input('ids');

        // Calculate true unpaid purchase balance (taking returns into account)
        $payableSub = \App\Models\Purchase::query()->toBase()
            ->leftJoinSub(
                \App\Models\PurchaseReturn::query()->toBase()
                    ->select('purchase_id', DB::raw('SUM(receivable_amount) as total_return_receivable'))
                    ->groupBy('purchase_id'),
                'pr_sum',
                'pr_sum.purchase_id',
                '=',
                'purchases.id'
            )
            ->select(
                'supplier_id',
                DB::raw('SUM(GREATEST(0, (payable_amount - COALESCE(pr_sum.total_return_receivable, 0)) - paid_amount)) as payable_total')
            )
            ->groupBy('supplier_id');

        // Calculate un-offset cash receivables owed by supplier from returns
        $receivableSub = \App\Models\PurchaseReturn::query()->toBase()
            ->join('purchases', 'purchases.id', '=', 'purchase_returns.purchase_id')
            ->select(
                'purchases.supplier_id',
                DB::raw('SUM(GREATEST(0, (purchase_returns.receivable_amount - GREATEST(0, purchases.payable_amount - purchases.paid_amount)) - purchase_returns.received_amount)) as receivable_total')
            )
            ->groupBy('purchases.supplier_id');


        return Supplier::query()
            ->leftJoinSub($payableSub, 'p', function ($join) {
                $join->on('p.supplier_id', '=', 'suppliers.id');
            })
            ->leftJoinSub($receivableSub, 'r', function ($join) {
                $join->on('r.supplier_id', '=', 'suppliers.id');
            })
            ->select(
                'suppliers.*',
                DB::raw('COALESCE(p.payable_total, 0) as payable_total'),
                DB::raw('COALESCE(r.receivable_total, 0) as receivable_total')
            )
            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('suppliers.id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('suppliers.name', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.phone', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.email', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.company_name', 'like', '%'.$search.'%');
                });
            })
            ->orderByRaw('suppliers.id desc');
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

