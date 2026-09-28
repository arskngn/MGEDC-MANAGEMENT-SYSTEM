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
        Schema::dropIfExists('batch_disposals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('batch_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('product_batches')->cascadeOnDelete();
            $table->date('disposal_date');
            $table->string('reason'); // expired, damaged_in_storage, spoiled, recall
            $table->integer('quantity_disposed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
