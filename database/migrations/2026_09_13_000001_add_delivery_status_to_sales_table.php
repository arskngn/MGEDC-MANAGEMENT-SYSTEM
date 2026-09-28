<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->enum('delivery_status', [
                'not_dispatched',
                'in_truck',
                'out_for_delivery',
                'delivered',
                'failed_delivery',
                'returned',
            ])->default('not_dispatched')->after('status');

            $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            $table->string('delivery_note', 500)->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'delivered_at', 'delivery_note']);
        });
    }
};
