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
        Schema::create('stock_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            // warehouse_id removed
            $table->foreignId('product_batch_id')->nullable()->constrained()->nullOnDelete();
            
            $table->nullableMorphs('reference'); // e.g. App\Models\Sale, App\Models\Purchase
            $table->string('transaction_type'); // 'sale', 'purchase', 'adjustment', 'transfer_in', 'transfer_out'
            
            $table->decimal('quantity_change', 10, 2);
            $table->decimal('running_balance', 10, 2);
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger_entries');
    }
};
