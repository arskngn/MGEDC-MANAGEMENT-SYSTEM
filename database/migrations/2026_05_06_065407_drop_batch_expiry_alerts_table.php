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
        Schema::dropIfExists('batch_expiry_alerts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('batch_expiry_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('product_batches')->cascadeOnDelete();
            $table->date('alert_date');
            $table->string('status')->default('pending'); // pending, notified, dismissed, expired
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();
        });
    }
};
