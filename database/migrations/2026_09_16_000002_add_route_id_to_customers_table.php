<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('customers', 'route_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('route_id')->nullable()->after('store_category_id')->constrained('customer_routes')->nullOnDelete();
            });
        }

        // Seed a fallback default route if none exists and assign it to existing customers without a route
        if (Schema::hasTable('customer_routes') && Schema::hasTable('customers')) {
            $defaultRouteId = DB::table('customer_routes')->value('id');

            if (!$defaultRouteId) {
                $defaultRouteId = DB::table('customer_routes')->insertGetId([
                    'name' => 'Main Route',
                    'from_location' => 'Warehouse',
                    'to_location' => 'City Center',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($defaultRouteId) {
                DB::table('customers')
                    ->whereNull('route_id')
                    ->update(['route_id' => $defaultRouteId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('customers', 'route_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['route_id']);
                $table->dropColumn('route_id');
            });
        }
    }
};
