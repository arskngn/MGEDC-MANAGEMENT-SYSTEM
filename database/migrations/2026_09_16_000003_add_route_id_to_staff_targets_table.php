<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff_targets')) {
            Schema::table('staff_targets', function (Blueprint $table) {
                if (!Schema::hasColumn('staff_targets', 'route_id')) {
                    $table->foreignId('route_id')->nullable()->after('user_id')->constrained('customer_routes')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('staff_targets')) {
            Schema::table('staff_targets', function (Blueprint $table) {
                if (Schema::hasColumn('staff_targets', 'route_id')) {
                    $table->dropForeign(['route_id']);
                    $table->dropColumn('route_id');
                }
            });
        }
    }
};
