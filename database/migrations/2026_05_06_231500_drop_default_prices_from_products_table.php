<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'default_sale_price')) {
                $table->dropColumn('default_sale_price');
            }

            if (Schema::hasColumn('products', 'default_purchase_price')) {
                $table->dropColumn('default_purchase_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'default_purchase_price')) {
                $table->decimal('default_purchase_price', 15, 2)->default(0)->after('unit_id');
            }

            if (! Schema::hasColumn('products', 'default_sale_price')) {
                $table->decimal('default_sale_price', 15, 2)->default(0)->after('default_purchase_price');
            }
        });
    }
};
