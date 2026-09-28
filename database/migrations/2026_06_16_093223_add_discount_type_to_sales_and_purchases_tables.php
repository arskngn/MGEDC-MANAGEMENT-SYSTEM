<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount_rate');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('discount_type');
        });
    }
};
