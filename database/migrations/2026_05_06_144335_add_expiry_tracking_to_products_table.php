<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_expiry')->default(false)->after('note');
            $table->integer('shelf_life_days')->nullable()->after('has_expiry');
            $table->integer('expiry_alert_days')->default(30)->after('shelf_life_days');
            $table->boolean('requires_batch_tracking')->default(false)->after('expiry_alert_days');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['has_expiry', 'shelf_life_days', 'expiry_alert_days', 'requires_batch_tracking']);
        });
    }
};
