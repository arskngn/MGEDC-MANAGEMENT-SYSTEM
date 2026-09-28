<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportProductsRequest;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Unit;
use App\Services\ProductService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use ResolvesCsvUploadPath;

    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $products = $this->filteredProductsQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        $categories = getCachedCategories();
        $brands = getCachedBrands();

        if (!$categories instanceof \Illuminate\Database\Eloquent\Collection) {
            $categories = Category::orderBy('name')->get();
        }
        if (!$brands instanceof \Illuminate\Database\Eloquent\Collection) {
            $brands = Brand::orderBy('name')->get();
        }

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function create(): View
    {
        $categories = getCachedCategories();
        $brands = getCachedBrands();
        $units = getCachedUnits();
        
        // Ensure lookups are proper collections of model objects (fallback if cache is corrupted)
        if (!$categories instanceof \Illuminate\Database\Eloquent\Collection) {
            $categories = Category::orderBy('name')->get();
        }
        if (!$brands instanceof \Illuminate\Database\Eloquent\Collection) {
            $brands = Brand::orderBy('name')->get();
        }
        if (!$units instanceof \Illuminate\Database\Eloquent\Collection) {
            $units = Unit::orderBy('name')->get();
        }
        
        return view('products.create', [
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->createProduct($request->validated());

        return redirect()->route('products.all')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $categories = getCachedCategories();
        $brands = getCachedBrands();
        $units = getCachedUnits();
        
        // Ensure lookups are proper collections of model objects (fallback if cache is corrupted)
        if (!$categories instanceof \Illuminate\Database\Eloquent\Collection) {
            $categories = Category::orderBy('name')->get();
        }
        if (!$brands instanceof \Illuminate\Database\Eloquent\Collection) {
            $brands = Brand::orderBy('name')->get();
        }
        if (!$units instanceof \Illuminate\Database\Eloquent\Collection) {
            $units = Unit::orderBy('name')->get();
        }
        
        return view('products.edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->updateProduct($product, $request->validated());

        return redirect()->route('products.all')->with('success', 'Product updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $products = $this->filteredProductsQuery($request)->get();
        
        $generalSetting = GeneralSetting::first();

        $pdfService = app(PdfService::class);
        $data = [
            'products' => $products,
            'generalSetting' => $generalSetting,
            'logoSrc' => $pdfService->logoDataUri($generalSetting),
        ];
        $filename = 'products-'.time().'.pdf';

        if ($request->has('print')) {
            return $pdfService->stream('products.pdf.all', $data, $filename, 'landscape');
        }

        return $pdfService->download('products.pdf.all', $data, $filename, 'landscape');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $products = $this->filteredProductsQuery($request)->get();

        return $this->productService->exportToCsv($products);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'product.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, \chr(0xEF).\chr(0xBB).\chr(0xBF));
            fputcsv($out, ['name', 'category', 'sku', 'brand', 'unit', 'alert_quantity', 'note']);
            fputcsv($out, ['Sample Product', 'Electronics', 'SKU001', 'Panasonic', 'piece', '10', '']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportProductsRequest $request): RedirectResponse
    {
        $path = $this->csvUploadPath($request->file('csv_file'));
        $result = $this->productService->importFromCsv($path);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if (!empty($result['errors'])) {
            $msg = "Imported {$result['imported']} products. Errors: " . implode(' ', $result['errors']);
            return redirect()->route('products.all')->with('error', $msg);
        }

        return redirect()->route('products.all')->with('success', "{$result['imported']} products imported successfully.");
    }

    private function filteredProductsQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');
        $stockStatus = $request->input('stock_status');
        $ids = $request->input('ids');

        return Product::query()
            ->with(['category', 'brand', 'unit'])
            ->when($ids, function ($q) use ($ids) {
                $q->whereIn('id', is_array($ids) ? $ids : explode(',', $ids));
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($brandId, function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            })
            ->when($stockStatus, function ($q) use ($stockStatus) {
                if ($stockStatus === 'in_stock') {
                    $q->where('current_stock', '>', 0);
                } elseif ($stockStatus === 'out_of_stock') {
                    $q->where('current_stock', '<=', 0);
                } elseif ($stockStatus === 'low_stock') {
                    $q->whereColumn('current_stock', '<=', 'alert_quantity');
                }
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('unit', function ($uq) use ($search) {
                            $uq->where('name', 'like', '%'.$search.'%')
                                ->orWhere('short_name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->latest('id');
    }



    public function storeBatch(StoreBatchRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $validated['product_id'] = $product->id;
        $validated['quantity_sold'] = 0;

        $validated['status'] = 'active';

        ProductBatch::create($validated);

        return redirect()->route('products.edit', $product)->with('success', 'Batch added successfully.');
    }

    public function updateBatch(StoreBatchRequest $request, Product $product, ProductBatch $batch): RedirectResponse
    {
        // Verify batch belongs to this product
        if ($batch->product_id !== $product->id) {
            return redirect()->route('products.edit', $product)->with('error', 'Batch not found for this product.');
        }

        $batch->update($request->validated());

        return redirect()->route('products.edit', $product)->with('success', 'Batch updated successfully.');
    }

    public function deleteBatch(Product $product, ProductBatch $batch): RedirectResponse
    {
        // Verify batch belongs to this product
        if ($batch->product_id !== $product->id) {
            return redirect()->route('products.edit', $product)->with('error', 'Batch not found for this product.');
        }

        $batch->delete();

        return redirect()->route('products.edit', $product)->with('success', 'Batch deleted successfully.');
    }
}
