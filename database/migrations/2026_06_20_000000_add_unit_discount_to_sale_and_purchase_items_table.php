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
                $table->decimal('unit_discount', 15, 2)->default(0)->after('unit_price');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_items', 'unit_discount')) {
                $table->decimal('unit_discount', 15, 2)->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'unit_discount')) {
                $table->dropColumn('unit_discount');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'unit_discount')) {
                $table->dropColumn('unit_discount');
            }
        });
    }
};
