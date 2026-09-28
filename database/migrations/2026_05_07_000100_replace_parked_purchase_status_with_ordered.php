<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('purchases')
            ->where('status', 'parked')
            ->update(['status' => 'ordered']);
    }

    public function down(): void
    {
        // Intentionally no-op: cannot safely infer original "parked" rows.
    }
};
