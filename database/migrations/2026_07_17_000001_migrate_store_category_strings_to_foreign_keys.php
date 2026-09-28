<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('store_categories')) {
            Schema::create('store_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasColumn('customers', 'store_category_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('store_category_id')->nullable()->after('address')->constrained('store_categories')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('sales', 'store_category_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('store_category_id')->nullable()->after('customer_id')->constrained('store_categories')->nullOnDelete();
            });
        }

        if (Schema::hasColumn('customers', 'store_category')) {
            $customerCategories = DB::table('customers')
                ->whereNotNull('store_category')
                ->where('store_category', '!=', '')
                ->select('id', 'store_category')
                ->get();

            foreach ($customerCategories as $row) {
                $normalized = trim((string) preg_replace('/\s+/', ' ', $row->store_category));
                if ($normalized === '') {
                    continue;
                }

                $categoryId = DB::table('store_categories')
                    ->where('name', $normalized)
                    ->value('id');

                if (!$categoryId) {
                    $categoryId = DB::table('store_categories')->insertGetId(['name' => $normalized, 'created_at' => now(), 'updated_at' => now()]);
                }

                DB::table('customers')
                    ->where('id', $row->id)
                    ->update(['store_category_id' => $categoryId]);
            }
        }

        if (Schema::hasColumn('sales', 'store_category')) {
            $saleCategories = DB::table('sales')
                ->whereNotNull('store_category')
                ->where('store_category', '!=', '')
                ->select('id', 'store_category')
                ->get();

            foreach ($saleCategories as $row) {
                $normalized = trim((string) preg_replace('/\s+/', ' ', $row->store_category));
                if ($normalized === '') {
                    continue;
                }

                $categoryId = DB::table('store_categories')
                    ->where('name', $normalized)
                    ->value('id');

                if (!$categoryId) {
                    $categoryId = DB::table('store_categories')->insertGetId(['name' => $normalized, 'created_at' => now(), 'updated_at' => now()]);
                }

                DB::table('sales')
                    ->where('id', $row->id)
                    ->update(['store_category_id' => $categoryId]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'store_category_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['store_category_id']);
                $table->dropColumn('store_category_id');
            });
        }

        if (Schema::hasColumn('sales', 'store_category_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['store_category_id']);
                $table->dropColumn('store_category_id');
            });
        }
    }
};
