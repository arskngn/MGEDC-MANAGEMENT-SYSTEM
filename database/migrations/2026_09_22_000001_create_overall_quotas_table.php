<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('overall_quotas')) {
            Schema::create('overall_quotas', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->decimal('target_amount', 15, 2);
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('commission_per_staff', 10, 2)->default(5000.00);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['start_date', 'end_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('overall_quotas');
    }
};
