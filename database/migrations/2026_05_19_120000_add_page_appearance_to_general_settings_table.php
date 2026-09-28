<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('welcome_scheme')->default(1)->after('default_theme');
            $table->json('welcome_colors')->nullable()->after('welcome_scheme');
            $table->unsignedTinyInteger('login_scheme')->default(1)->after('welcome_colors');
            $table->json('login_colors')->nullable()->after('login_scheme');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['welcome_scheme', 'welcome_colors', 'login_scheme', 'login_colors']);
        });
    }
};
