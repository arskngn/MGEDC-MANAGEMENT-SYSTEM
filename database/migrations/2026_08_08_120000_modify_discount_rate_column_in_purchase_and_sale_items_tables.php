<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE purchase_items MODIFY discount_rate DECIMAL(15,2) NOT NULL DEFAULT 0.00");
        DB::statement("ALTER TABLE sale_items MODIFY discount_rate DECIMAL(15,2) NOT NULL DEFAULT 0.00");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_items MODIFY discount_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00");
        DB::statement("ALTER TABLE sale_items MODIFY discount_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00");
    }
};
