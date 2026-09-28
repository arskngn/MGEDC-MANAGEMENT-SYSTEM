<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set a default whole company name for existing settings rows
        DB::table('general_settings')
            ->whereNull('whole_company_name')
            ->orWhere('whole_company_name', '')
            ->update(['whole_company_name' => 'Mindoro Golden Eagle Distribution Corporation']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('general_settings')
            ->where('whole_company_name', 'Mindoro Golden Eagle Distribution Corporation')
            ->update(['whole_company_name' => null]);
    }
};
