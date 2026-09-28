<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('purchase_items', 'tax_rate')) {
                $columnsToDrop[] = 'tax_rate';
            }
            if (Schema::hasColumn('purchase_items', 'tax_amount')) {
                $columnsToDrop[] = 'tax_amount';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_price');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate');
        });
    }
};
