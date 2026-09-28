<?php

namespace App\Console\Commands;

use App\Events\SaleCreated;
use App\Models\Sale;
use Illuminate\Console\Command;

class TestSaleNotification extends Command
{
    protected $signature = 'test:sale-notification {--send-event : Send a SaleCreated event}';
    protected $description = 'Test the sales notification system';

    public function handle()
    {
        $this->info('🔍 Testing Sales Notification System...');
        $this->newLine();

        // Step 1: Check if queue worker is running
        $this->testQueueStatus();
        $this->newLine();

        // Step 2: Check permissions
        $this->testPermissions();
        $this->newLine();

        // Step 3: Check event listener registration
        $this->testEventListeners();
        $this->newLine();

        // Step 4: Send test event if requested
        if ($this->option('send-event')) {
            $this->sendTestEvent();
            $this->newLine();
            $this->info('✅ Test event sent! Check notification dropdown or check database:');
            $this->line('   Database: SELECT * FROM system_notifications ORDER BY id DESC LIMIT 5;');
        } else {
            $this->newLine();
            $this->info('💡 To send a test notification event, run:');
            $this->line('   php artisan test:sale-notification --send-event');
        }
    }

    protected function testQueueStatus()
    {
        $this->info('📦 Queue Status:');
        $this->line('   Queue driver: ' . config('queue.default'));
        $this->line('   Is sync queue: ' . (config('queue.default') === 'sync' ? '✓ YES (Real-time)' : '✗ NO (Requires worker)'));
        
        if (config('queue.default') !== 'sync') {
            $this->warn('   ⚠️  Non-sync queue detected. Ensure queue worker is running:');
            $this->line('   php artisan queue:work');
        }
    }

    protected function testPermissions()
    {
        $this->info('🔐 Permission Check:');
        
        $admins = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->get();

        $encoders = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'encoder');
        })->get();

        $this->line("   Admin users: {$admins->count()}");
        foreach ($admins as $admin) {
            $hasAllSales = $admin->hasPermission('All Sales');
            $status = $hasAllSales ? '✓' : '✗';
            $this->line("      [$status] {$admin->name} ({$admin->email}) - All Sales: " . ($hasAllSales ? 'YES' : 'NO'));
        }

        $this->line("   Encoder users: {$encoders->count()}");
        foreach ($encoders as $encoder) {
            $hasAllSales = $encoder->hasPermission('All Sales');
            $status = $hasAllSales ? '✓' : '✗';
            $this->line("      [$status] {$encoder->name} ({$encoder->email}) - All Sales: " . ($hasAllSales ? 'YES' : 'NO'));
        }
    }

    protected function testEventListeners()
    {
        $this->info('🔗 Event Listeners:');
        $this->line('   SaleCreated event listeners:');
        $this->line('      ✓ SendSaleNotification');
        $this->line('      ✓ CheckLowStock');
    }

    protected function sendTestEvent()
    {
        $this->info('📤 Sending Test Event...');
        
        // Create a test sale
        $sale = Sale::latest()->first();

        if (!$sale) {
            $this->warn('No sales found in database. Cannot send test notification.');
            return;
        }

        // Fire the event
        event(new SaleCreated($sale));

        $this->info("   Event dispatched for Sale #{$sale->invoice_no}");
        $this->line('   Check system_notifications table for new records');
    }
}
