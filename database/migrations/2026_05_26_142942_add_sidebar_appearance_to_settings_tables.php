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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->tinyInteger('sidebar_scheme')->unsigned()->default(1)->after('login_colors');
            $table->json('sidebar_colors')->nullable()->after('sidebar_scheme');
        });

        Schema::table('user_appearance_settings', function (Blueprint $table) {
            $table->tinyInteger('sidebar_scheme')->unsigned()->default(1)->after('login_colors');
            $table->json('sidebar_colors')->nullable()->after('sidebar_scheme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['sidebar_scheme', 'sidebar_colors']);
        });

        Schema::table('user_appearance_settings', function (Blueprint $table) {
            $table->dropColumn(['sidebar_scheme', 'sidebar_colors']);
        });
    }
};
