<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn(['quantity_alert_sent', 'storage_temperature']);
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->integer('quantity_alert_sent')->default(0)->after('quantity_adjusted');
            $table->float('storage_temperature')->nullable()->after('status');
        });
    }
};
