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
        // Dropping and re-adding is the most cross-database compatible way (supports both SQLite and MySQL without doctrine/dbal)
        Schema::table('adjustment_items', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('adjustment_items', function (Blueprint $table) {
            $table->string('type')->after('unit_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adjustment_items', function (Blueprint $table) {
            //
        });
    }
};
