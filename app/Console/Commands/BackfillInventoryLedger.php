<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductBatch;
use App\Models\Product;
use App\Actions\Inventory\LogStockTransactionAction;
use Illuminate\Support\Facades\DB;

class BackfillInventoryLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:backfill-ledger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfills the Append-Only Inventory Ledger (stock_ledger_entries) from existing ProductBatch data.';

    /**
     * Execute the console command.
     */
    public function handle(LogStockTransactionAction $logAction)
    {
        $this->info('Starting inventory ledger backfill...');

        // Truncate outside transaction because DDL statements implicitly commit in MySQL
        DB::table('stock_ledger_entries')->truncate();

        DB::transaction(function () use ($logAction) {
            // Group existing active batches by product only
            $stocks = ProductBatch::where('quantity_available', '>', 0)
                ->selectRaw('product_id, SUM(quantity_available) as total_qty')
                ->groupBy('product_id')
                ->get();

            $bar = $this->output->createProgressBar(count($stocks));

            foreach ($stocks as $stock) {
                $product = Product::find($stock->product_id);
                if ($product) {
                    $logAction->execute(
                        $product,
                        (float)$stock->total_qty,
                        'initial_backfill',
                        null,
                        null,
                        'System backfill from existing ProductBatch data'
                    );
                }
                $bar->advance();
            }

            $bar->finish();
        });

        $this->info("\nInventory ledger backfill completed successfully.");
    }
}
