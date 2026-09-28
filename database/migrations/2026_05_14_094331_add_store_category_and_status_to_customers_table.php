<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('store_categories')) {
            Schema::create('store_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'store_category_id')) {
                $table->foreignId('store_category_id')->nullable()->after('address')->constrained('store_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('customers', 'status')) {
                $table->string('status')->default('active')->after('store_category_id');
            }
        });

        DB::table('customers')->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['store_category', 'status']);
        });
    }
};
