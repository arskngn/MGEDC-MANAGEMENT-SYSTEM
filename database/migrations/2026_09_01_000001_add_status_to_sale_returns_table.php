<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_returns', 'status')) {
                $table->string('status', 20)->default('pending')->after('note');
            }
        });

        // Existing returns were already processed — mark them as confirmed
        DB::table('sale_returns')->update(['status' => 'confirmed']);
    }

    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            if (Schema::hasColumn('sale_returns', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
