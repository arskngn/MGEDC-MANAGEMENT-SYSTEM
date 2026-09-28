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
        if (Schema::hasColumn('product_batches', 'status')) {
            return;
        }

        Schema::table('product_batches', function (Blueprint $table) {
            $table->string('status')->default('active')->after('batch_number');
        });

        DB::table('product_batches')->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('product_batches', 'status')) {
            return;
        }

        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
