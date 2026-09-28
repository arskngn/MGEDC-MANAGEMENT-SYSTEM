<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_return_item_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_item_id')->constrained('sale_return_items')->cascadeOnDelete();
            $table->foreignId('product_batch_id')->constrained('product_batches')->cascadeOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->string('condition', 20)->default('good'); // good, damaged
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_item_batches');
    }
};
