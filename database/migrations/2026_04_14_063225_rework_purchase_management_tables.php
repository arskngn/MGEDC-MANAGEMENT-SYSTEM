<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('discount');
            }
            if (!Schema::hasColumn('purchases', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            }
            if (!Schema::hasColumn('purchases', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('purchases', 'actual_delivery_date')) {
                $table->date('actual_delivery_date')->nullable()->after('expected_delivery_date');
            }
            if (!Schema::hasColumn('purchases', 'status')) {
                $table->string('status')->default('received')->after('payable_amount'); // ordered, received, partial
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('purchase_items', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
            }
            if (!Schema::hasColumn('purchase_items', 'product_batch_id')) {
                $table->foreignId('product_batch_id')->nullable()->constrained()->onDelete('set null')->after('purchase_id');
            }
        });

    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_batch_id');
            $table->dropColumn(['tax_rate', 'tax_amount']);
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'tax_amount', 'expected_delivery_date', 'actual_delivery_date', 'status']);
        });
    }
};
