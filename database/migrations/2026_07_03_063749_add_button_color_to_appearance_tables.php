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
        Schema::table('user_appearance_settings', function (Blueprint $table) {
            $table->string('button_color', 7)->nullable()->after('sidebar_colors');
        });
        
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('button_color', 7)->nullable()->after('sidebar_colors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_appearance_settings', function (Blueprint $table) {
            $table->dropColumn('button_color');
        });
        
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('button_color');
        });
    }
};
