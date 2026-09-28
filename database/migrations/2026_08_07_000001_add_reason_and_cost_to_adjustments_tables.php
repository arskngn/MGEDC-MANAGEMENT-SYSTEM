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
        Schema::table('adjustments', function (Blueprint $table) {
            $table->string('reason', 100)->nullable()->after('note');
        });

        Schema::table('adjustment_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 4)->default(0)->after('quantity');
            $table->decimal('subtotal_cost', 15, 2)->default(0)->after('unit_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjustments', function (Blueprint $table) {
            $table->dropColumn('reason');
        });

        Schema::table('adjustment_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'subtotal_cost']);
        });
    }
};
