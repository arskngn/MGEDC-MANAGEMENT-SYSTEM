<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->date('received_date')->nullable()->after('manufactured_date');
            $table->integer('quantity_available')->default(0)->after('quantity');
            $table->integer('quantity_returned')->default(0)->after('quantity_sold');
            $table->integer('quantity_adjusted')->default(0)->after('quantity_returned');
            $table->integer('quantity_expired')->default(0)->after('quantity_adjusted');
            $table->float('storage_temperature')->nullable()->after('status');
            $table->integer('alert_days_before_expiry')->default(30)->after('storage_temperature');
        });

        Schema::create('batch_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // received, sold, returned, adjusted, expired_writeoff
            $table->integer('quantity_change');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable(); // Sale, Purchase, Adjustment, etc.
            $table->date('transaction_date');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('batch_expiry_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained()->onDelete('cascade');
            $table->integer('days_until_expiry');
            $table->string('alert_status')->default('pending'); // pending, notified, dismissed, expired
            $table->date('notified_date')->nullable();
            $table->json('notified_users')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('batch_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_batch_id')->constrained()->onDelete('cascade');
            $table->string('disposal_type'); // expired, damaged_in_storage, spoiled, recall
            $table->integer('quantity_disposed');
            $table->date('disposal_date');
            $table->string('disposal_method')->nullable(); // destroyed, recycled, donated
            $table->foreignId('approved_by_user_id')->constrained('users')->onDelete('cascade');
            $table->text('reason_notes')->nullable();
            $table->decimal('cost_impact', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_disposals');
        Schema::dropIfExists('batch_expiry_alerts');
        Schema::dropIfExists('batch_stock_logs');
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_id');
            $table->dropColumn([
                'received_date',
                'quantity_available',
                'quantity_returned',
                'quantity_adjusted',
                'quantity_expired',
                'storage_temperature',
                'alert_days_before_expiry'
            ]);
        });
    }
};
