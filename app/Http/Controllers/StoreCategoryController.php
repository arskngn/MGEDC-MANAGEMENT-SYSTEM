<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\StoreStoreCategoryRequest;
use App\Http\Requests\UpdateStoreCategoryRequest;
use App\Models\StoreCategory;
use App\Models\GeneralSetting;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoreCategoryController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();

        $storeCategories = StoreCategory::query()
            ->withCount('customers')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('store_categories.index', [
            'storeCategories' => $storeCategories,
        ]);
    }

    public function store(StoreStoreCategoryRequest $request): RedirectResponse
    {
        StoreCategory::create(['name' => $request->validated()['name']]);
        invalidateLookupCaches();

        return redirect()->route('store-categories.index')->with('success', 'Store category created successfully.');
    }

    public function update(UpdateStoreCategoryRequest $request, StoreCategory $storeCategory): RedirectResponse
    {
        $storeCategory->update(['name' => $request->validated()['name']]);
        invalidateLookupCaches();

        return redirect()->route('store-categories.index')->with('success', 'Store category updated successfully.');
    }

    public function destroy(StoreCategory $storeCategory): RedirectResponse
    {
        if ($storeCategory->customers()->exists()) {
            return redirect()->route('store-categories.index')->with('error', 'Cannot delete a store category that still has customers assigned. Reassign those customers first.');
        }

        $storeCategory->delete();
        invalidateLookupCaches();

        return redirect()->route('store-categories.index')->with('success', 'Store category deleted successfully.');
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name']);
            fputcsv($out, ['Sample Store Category']);
            fclose($out);
        }, 'store-category.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path = $this->csvUploadPath($request->file('csv_file'));
        $opened = CsvImportReader::open($path);
        if ($opened === null) {
            return back()->with('error', 'Could not read the uploaded file or the CSV is empty.');
        }

        $handle = $opened['handle'];
        $delimiter = $opened['delimiter'];
        $header = $opened['header'];

        $map = [];
        foreach ($header as $i => $col) {
            $map[strtolower(trim((string) $col))] = $i;
        }

        if (! isset($map['name'])) {
            fclose($handle);

            return back()->with('error', 'Missing required column: name. Use the sample template.');
        }

        $imported = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $name = trim((string) ($row[$map['name']] ?? ''));
            if ($name === '') {
                $errors[] = "Row {$rowNum}: name is required.";

                continue;
            }

            if (StoreCategory::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: \"{$name}\" already exists.";

                continue;
            }

            try {
                StoreCategory::create(['name' => $name]);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);
        invalidateLookupCaches();

        if ($imported === 0 && $errors === []) {
            return back()->with('error', 'No data rows were found in the file.');
        }

        $msg = "Imported {$imported} store categor".($imported === 1 ? 'y' : 'ies').'.';
        if ($errors !== []) {
            $msg .= ' '.count($errors).' row(s) skipped: '.implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $msg .= ' …';
            }
        }

        return back()->with('success', $msg);
    }

    private function csvRowIsEmpty(?array $row): bool
    {
        if ($row === null || $row === []) {
            return true;
        }
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
