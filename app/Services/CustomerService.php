<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\StoreCategory;
use App\Traits\CsvSanitization;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    use CsvSanitization;

    public function __construct(protected ImportExportService $importExportService)
    {
    }

    /**
     * Normalize a store category string.
     */
    protected function normalizeStoreCategory(?string $category): ?string
    {
        if ($category === null || trim($category) === '') {
            return null;
        }
        
        $clean = trim($category);
        $clean = preg_replace('/\s+/', ' ', $clean);
        return $clean;
    }

    /**
     * Save a store category if it doesn't exist.
     */
    protected function saveStoreCategory(?string $category): ?int
    {
        $normalized = $this->normalizeStoreCategory($category);
        
        if ($normalized) {
            $storeCategory = StoreCategory::firstOrCreate(['name' => $normalized]);

            return $storeCategory->id;
        }

        return null;
    }

    /**
     * Store a new customer.
     */
    public function createCustomer(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
            'store_category_id' => $this->saveStoreCategory($data['store_category'] ?? null),
            'route_id' => $data['route_id'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Update an existing customer.
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
            'store_category_id' => $this->saveStoreCategory($data['store_category'] ?? null),
            'route_id' => $data['route_id'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return $customer;
    }

    /**
     * Export customers to CSV.
     */
    public function exportToCsv(iterable $customers): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'name', 'email', 'mobile', 'address', 'route', 'receivable', 'payable'
        ];

        $rows = [];
        foreach ($customers as $c) {
            $rows[] = [
                $c->name,
                $c->email ?? '',
                $c->phone ?? '',
                $c->address ?? '',
                $c->route?->display_name ?? '',
                (string)($c->receivable_total ?? 0),
                (string)($c->payable_total ?? 0),
            ];
        }

        // Sanitize rows to prevent formula injection in spreadsheet applications
        $sanitizedRows = $this->sanitizeCsvRows($rows);

        return $this->importExportService->downloadCsv('customers-' . time() . '.csv', $headers, $sanitizedRows);
    }

    /**
     * Import customers from CSV.
     */
    public function importFromCsv(string $path): array
    {
        $opened = $this->importExportService->openCsv($path);
        if (!$opened) {
            return ['error' => 'Could not read the uploaded file or the CSV is empty.'];
        }

        $handle = $opened['handle'];
        $delimiter = $opened['delimiter'];
        $map = $opened['map'];

        $required = ['name'];
        foreach ($required as $col) {
            if (!isset($map[$col])) {
                fclose($handle);
                return ['error' => 'Missing required column: ' . $col . '. Use the sample template.'];
            }
        }

        $rowNum = 1;
        $imported = 0;
        $skipped = 0;
        $firstError = null;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->importExportService->isRowEmpty($row)) {
                continue;
            }

            $name = trim((string)($row[$map['name']] ?? ''));
            $email = isset($map['email']) ? trim((string)($row[$map['email']] ?? '')) : null;
            $mobile = isset($map['mobile']) ? trim((string)($row[$map['mobile']] ?? '')) : null;
            $address = isset($map['address']) ? trim((string)($row[$map['address']] ?? '')) : null;

            if ($name === '') {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': name is required.';
                continue;
            }

            if ($mobile !== null && $mobile !== '') {
                $existsQuery = Customer::query()->where('phone', $mobile);
                if ($email !== '' && $email !== null) {
                    $existsQuery->orWhere('email', $email);
                }
                $exists = $existsQuery->exists();
                if ($exists) {
                    $skipped++;
                    $firstError ??= 'Row ' . $rowNum . ': customer with the same email or mobile already exists.';
                    continue;
                }
            }

            try {
                Customer::create([
                    'name' => $name,
                    'phone' => $mobile !== null && $mobile !== '' ? $mobile : null,
                    'email' => $email !== '' ? $email : null,
                    'address' => $address !== '' ? $address : null,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': failed to import (' . $e->getMessage() . ').';
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'firstError' => $firstError,
        ];
    }
}
