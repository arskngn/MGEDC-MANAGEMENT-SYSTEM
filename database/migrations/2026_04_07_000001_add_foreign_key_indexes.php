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

        if (Schema::hasTable('purchases') && ! $indexExists('purchases', 'supplier_id')) {
            $addIndex('purchases', 'supplier_id');
        }

        // warehouse_id indexes removed

        if (Schema::hasTable('sales') && ! $indexExists('sales', 'customer_id')) {
            $addIndex('sales', 'customer_id');
        }

        // warehouse_id indexes removed

        if (Schema::hasTable('purchase_returns') && ! $indexExists('purchase_returns', 'purchase_id')) {
            $addIndex('purchase_returns', 'purchase_id');
        }

        // warehouse_id indexes removed

        if (Schema::hasTable('sale_returns') && ! $indexExists('sale_returns', 'sale_id')) {
            $addIndex('sale_returns', 'sale_id');
        }

        // warehouse_id indexes removed
    }

    public function down(): void
    {
        $indexExists = $this->indexExists(...);
        $dropIndex = $this->dropIndex(...);

        if (Schema::hasTable('purchases') && $indexExists('purchases', 'supplier_id')) {
            $dropIndex('purchases', 'supplier_id');
        }

        // warehouse_id index drops removed

        if (Schema::hasTable('sales') && $indexExists('sales', 'customer_id')) {
            $dropIndex('sales', 'customer_id');
        }

        // warehouse_id index drops removed

        if (Schema::hasTable('purchase_returns') && $indexExists('purchase_returns', 'purchase_id')) {
            $dropIndex('purchase_returns', 'purchase_id');
        }

        // warehouse_id index drops removed

        if (Schema::hasTable('sale_returns') && $indexExists('sale_returns', 'sale_id')) {
            $dropIndex('sale_returns', 'sale_id');
        }

        // warehouse_id index drops removed
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
