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
                if (!Schema::hasColumn('staff_targets', 'store_category_ids')) {
                    $table->json('store_category_ids')->nullable()->after('route_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('staff_targets')) {
            Schema::table('staff_targets', function (Blueprint $table) {
                if (Schema::hasColumn('staff_targets', 'store_category_ids')) {
                    $table->dropColumn('store_category_ids');
                }
            });
        }
    }
};
