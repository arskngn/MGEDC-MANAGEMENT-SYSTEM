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
        Schema::table('product_batches', function (Blueprint $table) {
            // Drop expiration-related columns
            if (Schema::hasColumn('product_batches', 'expiration_date')) {
                $table->dropIndex(['expiration_date']);
                $table->dropColumn('expiration_date');
            }
            if (Schema::hasColumn('product_batches', 'manufactured_date')) {
                $table->dropColumn('manufactured_date');
            }
            if (Schema::hasColumn('product_batches', 'received_date')) {
                $table->dropColumn('received_date');
            }
            if (Schema::hasColumn('product_batches', 'quantity_expired')) {
                $table->dropColumn('quantity_expired');
            }
            if (Schema::hasColumn('product_batches', 'alert_days_before_expiry')) {
                $table->dropColumn('alert_days_before_expiry');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->date('expiration_date')->nullable()->index();
            $table->date('manufactured_date')->nullable();
            $table->date('received_date')->nullable();
            $table->integer('quantity_expired')->default(0);
            $table->integer('alert_days_before_expiry')->default(30);
        });
    }
};
