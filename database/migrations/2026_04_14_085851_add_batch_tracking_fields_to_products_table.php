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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('shelf_life_days')->nullable()->after('note');
            $table->boolean('requires_batch_tracking')->default(false)->after('shelf_life_days');
            $table->integer('expiry_alert_days')->default(30)->after('requires_batch_tracking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['shelf_life_days', 'requires_batch_tracking', 'expiry_alert_days']);
        });
    }
};
