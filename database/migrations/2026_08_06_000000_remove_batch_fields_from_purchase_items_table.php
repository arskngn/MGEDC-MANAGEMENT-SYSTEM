<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_items', 'product_batch_id')) {
                $table->dropConstrainedForeignId('product_batch_id');
            }

            $columnsToDrop = [];
            foreach (['batch_number', 'manufactured_date', 'expiration_date', 'expiry_date'] as $col) {
                if (Schema::hasColumn('purchase_items', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('product_batch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('batch_number')->nullable();
            $table->date('manufactured_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('expiry_date')->nullable();
        });
    }
};
