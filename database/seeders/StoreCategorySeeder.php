<?php

namespace Database\Seeders;

use App\Models\StoreCategory;
use Illuminate\Database\Seeder;

class StoreCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sari-sari store',
            'Convenience store',
            'Supermarket',
        ];

        foreach ($categories as $category) {
            StoreCategory::firstOrCreate(['name' => $category]);
        }
    }
}
