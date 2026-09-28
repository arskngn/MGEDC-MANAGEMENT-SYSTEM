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
        Schema::table('adjustment_items', function (Blueprint $table) {
            if (!Schema::hasColumn('adjustment_items', 'product_batch_id')) {
                $table->foreignId('product_batch_id')->nullable()->constrained()->onDelete('cascade')->after('product_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjustment_items', function (Blueprint $table) {
            //
        });
    }
};
