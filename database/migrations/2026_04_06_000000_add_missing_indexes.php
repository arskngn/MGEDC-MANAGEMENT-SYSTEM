<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexExists = $this->indexExists(...);
        $addIndex = $this->addIndex(...);

        if (Schema::hasTable('sales') && ! $indexExists('sales', 'invoice_no')) {
            $addIndex('sales', 'invoice_no');
        }

        if (Schema::hasTable('products') && ! $indexExists('products', 'sku')) {
            $addIndex('products', 'sku');
        }

        if (Schema::hasTable('customers') && ! $indexExists('customers', 'phone')) {
            $addIndex('customers', 'phone');
        }

        if (Schema::hasTable('suppliers') && ! $indexExists('suppliers', 'phone')) {
            $addIndex('suppliers', 'phone');
        }
    }

    public function down(): void
    {
        $indexExists = $this->indexExists(...);
        $dropIndex = $this->dropIndex(...);

        if (Schema::hasTable('sales') && $indexExists('sales', 'invoice_no')) {
            $dropIndex('sales', 'invoice_no');
        }

        if (Schema::hasTable('products') && $indexExists('products', 'sku')) {
            $dropIndex('products', 'sku');
        }

        if (Schema::hasTable('customers') && $indexExists('customers', 'phone')) {
            $dropIndex('customers', 'phone');
        }

        if (Schema::hasTable('suppliers') && $indexExists('suppliers', 'phone')) {
            $dropIndex('suppliers', 'phone');
        }
    }

    private function indexExists(string $table, string $column): bool
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return false;
        }

        $indexes = DB::select("SHOW INDEXES FROM `{$table}` WHERE Column_name = ?", [$column]);

        return count($indexes) > 0;
    }

    private function addIndex(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->index($column);
            });
        } catch (\Throwable) {
            // Index may already exist (e.g. SQLite tests).
        }
    }

    private function dropIndex(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropIndex([$column]);
            });
        } catch (\Throwable) {
            // Index may not exist.
        }
    }
};
