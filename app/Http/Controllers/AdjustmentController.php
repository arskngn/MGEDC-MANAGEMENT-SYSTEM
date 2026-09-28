<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdjustmentRequest;
use App\Http\Requests\UpdateAdjustmentRequest;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Services\AdjustmentService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdjustmentController extends Controller
{
    public function __construct(
        protected AdjustmentService $adjustmentService
    ) {}

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $adjustments = Adjustment::query()
            ->with(['items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('tracking_no', 'like', '%'.$search.'%');
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('adjustment_date', '<=', $dateTo))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('adjustments.index', [
            'adjustments' => $adjustments,
        ]);
    }

    public function create(): View
    {
        return view('adjustments.create', [
            'trackingNo' => Adjustment::nextTrackingNo(),
        ]);
    }

    public function store(StoreAdjustmentRequest $request): RedirectResponse
    {
        $this->adjustmentService->createAdjustment($request->validated());

        return redirect()->route('adjustments.index')->with('success', 'Adjustment created successfully.');
    }

    public function edit(Adjustment $adjustment): View
    {
        $adjustment->load(['items.product.unit', 'items.batch']);

        return view('adjustments.edit', [
            'adjustment' => $adjustment,
        ]);
    }

    public function update(UpdateAdjustmentRequest $request, Adjustment $adjustment): RedirectResponse
    {
        $this->adjustmentService->updateAdjustment($adjustment, $request->validated());

        return redirect()->route('adjustments.index')->with('success', 'Adjustment updated successfully.');
    }

    public function destroy(Adjustment $adjustment): RedirectResponse
    {
        $this->adjustmentService->deleteAdjustment($adjustment);

        return redirect()->route('adjustments.index')->with('success', 'Adjustment deleted successfully.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->string('q')->trim()->value();

        $products = Product::query()
            ->with(['unit', 'batches' => function ($query) {
                $query->whereIn('status', ['active', 'hold', 'quarantined'])
                    ->where('quantity_available', '>', 0);
            }])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('sku', 'like', '%'.$q.'%');
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'unit_label' => $p->unit->short_name ?? $p->unit->name,
                    'in_stock' => $p->current_stock,
                    'requires_batch_tracking' => (bool) $p->requires_batch_tracking,
                    'batches' => $p->batches->map(function ($b) {
                        return [
                            'id' => $b->id,
                            'batch_number' => $b->batch_number,
                            'available' => $b->quantity_available,
                        ];
                    }),
                ];
            });

        return response()->json($products);
    }

    public function exportPdfInvoice(Adjustment $adjustment, Request $request)
    {
        $adjustment->load(['items.product.unit', 'items.batch']);
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'adjustment' => $adjustment,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'adjustment-'.$adjustment->tracking_no.'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('adjustments.pdf.invoice', $data, $filename, 'portrait');
        }

        return $pdfService->download('adjustments.pdf.invoice', $data, $filename, 'portrait');
    }

    public function exportPdfAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $ids = $request->input('ids');

        $adjustments = Adjustment::query()
            ->with(['items.product.unit', 'items.batch'])
            ->when($ids, function ($q) use ($ids) {
                $idList = is_array($ids) ? $ids : explode(',', $ids);
                $q->whereIn('id', array_filter($idList));
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where('tracking_no', 'like', '%'.$search.'%');
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('adjustment_date', '<=', $dateTo))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->get();

        $type = $request->input('type');
        $isBatchInvoicePrint = ($request->has('ids') || $type === 'batch') && $type !== 'total';
        $view = $isBatchInvoicePrint ? 'adjustments.pdf.invoices-batch' : 'adjustments.pdf.all';

        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'adjustments' => $adjustments,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = ($isBatchInvoicePrint ? 'adjustment-invoices-' : 'all-adjustments-').now()->format('Y-m-d-His').'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream($view, $data, $filename, 'portrait');
        }

        return $pdfService->download($view, $data, $filename, 'portrait');
    }

    public function exportCsvAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $adjustments = Adjustment::query()
            ->with(['items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('tracking_no', 'like', '%'.$search.'%');
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('adjustment_date', '<=', $dateTo))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'adjustments-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($adjustments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.N.', 'Tracking No.', 'Date', 'Products', 'Note']);

            foreach ($adjustments as $i => $adjustment) {
                fputcsv($file, [
                    $i + 1,
                    $adjustment->tracking_no,
                    $adjustment->adjustment_date->format('d M, Y'),
                    count($adjustment->items),
                    $adjustment->note ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}
