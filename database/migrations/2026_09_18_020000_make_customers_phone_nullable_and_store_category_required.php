<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make phone nullable
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone')->nullable()->change();
        });

        // Ensure a default store category exists
        $defaultCatId = DB::table('store_categories')->value('id');
        if (!$defaultCatId) {
            $defaultCatId = DB::table('store_categories')->insertGetId([
                'name'       => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Backfill any customers that have no store category
        DB::table('customers')
            ->whereNull('store_category_id')
            ->update(['store_category_id' => $defaultCatId]);

        // MySQL requires the column to remain NULLABLE when a SET NULL foreign key
        // action exists on it (customers_store_category_id_foreign uses nullOnDelete).
        // We cannot make it NOT NULL; instead we set a DEFAULT so new rows always
        // get a category even if none is supplied.
        Schema::table('customers', function (Blueprint $table) use ($defaultCatId) {
            $table->unsignedBigInteger('store_category_id')
                ->nullable()          // must stay nullable because of the SET NULL FK
                ->default($defaultCatId)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('phone')->nullable(false)->change();
            $table->unsignedBigInteger('store_category_id')
                ->nullable()
                ->default(null)
                ->change();
        });
    }
};
