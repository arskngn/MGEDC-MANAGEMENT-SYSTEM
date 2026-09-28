<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'unit_discount')) {
                $table->decimal('unit_discount', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('sale_items', 'unit_discount_type')) {
                $table->string('unit_discount_type')->default('fixed');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_items', 'unit_discount')) {
                $table->decimal('unit_discount', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('purchase_items', 'unit_discount_type')) {
                $table->string('unit_discount_type')->default('fixed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'unit_discount_type')) {
                $table->dropColumn('unit_discount_type');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'unit_discount_type')) {
                $table->dropColumn('unit_discount_type');
            }
        });
    }
};
