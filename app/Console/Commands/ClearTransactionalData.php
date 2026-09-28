<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;

#[Signature('app:clear-transactional-data {--force : Skip confirmation prompts}')]
#[Description('Clear all transactional data: purchases, purchase returns, sales, batches, adjustments, transfers, expenses')]
class ClearTransactionalData extends Command
{
    /** @var list<string> */
    protected array $tables = [
        // Stock Ledger
        'stock_ledger_entries',
        
        // Batch Management
        'batch_stock_logs',
        'sale_item_batches',
        'sale_return_item_batches', // Added this!
        'purchase_return_item_batches',
        'product_batches',

        // Purchase Returns
        'purchase_return_items',
        'purchase_return_payments', // Added this!
        'purchase_returns',

        // Purchases
        'purchase_items',
        'purchase_payments',
        'purchases',
        
        // Sales Returns
        'sale_return_items',
        'sale_return_payments',
        'sale_returns',

        // Sales
        'sale_items',
        'sale_payments',
        'sales',
        
        // Adjustments
        'adjustment_items',
        'adjustments',
        
        // Transfers
        'transfer_items',
        'transfers',
        
        // Expenses
        'expenses',
        
        // Notifications
        'notification_logs',
        'system_notifications',
    ];

    public function handle()
    {
        $force = $this->option('force');

        if (!$force) {
            $this->warn('⚠️  WARNING: This will delete ALL transactional data!');
            $this->line('This includes: purchases, returns, sales, batches, adjustments, transfers, expenses, and notifications.');
            $this->line('');

            if (!$this->confirm('Are you ABSOLUTELY SURE you want to proceed?')) {
                $this->info('Operation cancelled. No data was deleted.');
                return 0;
            }

            if (!$this->confirm('This is irreversible. Do you really want to continue?')) {
                $this->info('Operation cancelled. No data was deleted.');
                return 0;
            }
        }

        $this->info('');
        $this->info('Clearing transactional data...');

        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            if (! Schema::hasTable($table)) {
                $this->line("⏭️  Skipped {$table}: table does not exist");

                continue;
            }

            $count = DB::table($table)->count();
            DB::table($table)->truncate();
            $this->line("✅ Cleared {$table}: {$count} records deleted");
        }

        Schema::enableForeignKeyConstraints();
        
        // Recalculate product stock levels
        $this->info('');
        $this->info('Recalculating product stock levels...');
        Product::all()->each(function (Product $product) {
            $product->updateStock();
        });

        $this->info('');
        $this->info('🎉 All transactional data has been cleared successfully!');

        return 0;
    }
}
