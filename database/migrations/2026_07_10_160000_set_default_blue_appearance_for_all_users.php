<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set blue (#1C3ABA) as the default system color for all users.
     * Clears all per-user appearance overrides so everyone gets the site default.
     */
    public function up(): void
    {
        $blueColors = json_encode(['#1C3ABA', '#1C3ABA', '#1E3A8A', '#1E3A8A', '#1C3ABA', '#1C3ABA']);

        // Update site-wide defaults in general_settings
        DB::table('general_settings')->update([
            'welcome_scheme' => 1,
            'welcome_colors' => $blueColors,
            'login_scheme'   => 1,
            'login_colors'   => $blueColors,
            'sidebar_scheme' => 1,
            'sidebar_colors' => $blueColors,
        ]);

        // Clear all per-user appearance overrides so everyone uses the site default
        DB::table('user_appearance_settings')->truncate();
    }

    public function down(): void
    {
        // Revert to original orange scheme 1
        $orangeColors = json_encode(['#F15A22', '#0A0E27', '#F15A22', '#0A0E27', '#F15A22', '#0A0E27']);

        DB::table('general_settings')->update([
            'welcome_scheme' => 1,
            'welcome_colors' => $orangeColors,
            'login_scheme'   => 1,
            'login_colors'   => $orangeColors,
            'sidebar_scheme' => 1,
            'sidebar_colors' => $orangeColors,
        ]);
    }
};
