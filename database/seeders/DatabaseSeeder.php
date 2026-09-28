<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear application cache to ensure dashboard and other cached data are reset
        Cache::flush();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            UnitSeeder::class,
            ExpenseTypeSeeder::class,
            StoreCategorySeeder::class,
            UserSeeder::class,
        ]);
    }
}
