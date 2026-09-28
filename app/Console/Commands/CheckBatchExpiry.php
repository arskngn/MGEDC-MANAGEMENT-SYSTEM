<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductBatch;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckBatchExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:batch-expiry {--days=7 : Days before expiry to send warning alerts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired batches, update their status, and trigger notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Starting batch expiry check...');

        $alertDays = (int) $this->option('days');

        // Find users who should receive these notifications (e.g., users who can manage products)
        $staffToNotify = User::whereHas('role.permissions', function($q) {
            $q->where('name', 'All Product');
        })->get();

        // ── 1. Flag batches whose expiry_date has passed ──
        $expiredBatches = ProductBatch::where('status', ProductBatch::STATUS_ACTIVE)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->toDateString())
            ->with('product')
            ->get();

        $expiredCount = 0;

        foreach ($expiredBatches as $batch) {
            // The saving hook in ProductBatch::boot() calls updateStatusAutomatically(),
            // which sets status to 'expired' when expiry_date is in the past.
            $batch->save();
            $expiredCount++;

            foreach ($staffToNotify as $staff) {
                $notificationService->sendToAdmin(
                    'Batch Expired',
                    "Batch #{$batch->batch_number} for product '{$batch->product->name}' has expired.",
                    'danger',
                    $staff->id
                );
            }
        }

        $this->info("Updated {$expiredCount} batches to expired status.");
        Log::info("CheckBatchExpiry: Updated {$expiredCount} batches to expired status.");

        // ── 2. Warn about batches expiring within the next N days ──
        $soonExpiringBatches = ProductBatch::where('status', ProductBatch::STATUS_ACTIVE)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', Carbon::now()->toDateString())
            ->with('product')
            ->get();

        $alertCount = 0;
        foreach ($soonExpiringBatches as $batch) {
            $daysLeft = Carbon::now()->startOfDay()->diffInDays(Carbon::parse($batch->expiry_date)->startOfDay());
            $batchAlertDays = $batch->alert_days_before_expiry ?? $batch->product->expiry_alert_days ?? $alertDays;

            if ($daysLeft <= $batchAlertDays) {
                foreach ($staffToNotify as $staff) {
                    $title = 'Batch Expiring Soon';
                    $message = "Batch #{$batch->batch_number} for product '{$batch->product->name}' will expire in {$daysLeft} day(s).";

                    // Skip if already notified this user about this batch today
                    $alreadySent = \App\Models\SystemNotification::where('user_id', $staff->id)
                        ->where('title', $title)
                        ->where('message', $message)
                        ->whereDate('created_at', Carbon::today())
                        ->exists();

                    if (!$alreadySent) {
                        $notificationService->sendToAdmin($title, $message, 'warning', $staff->id);
                        $alertCount++;
                    }
                }
            }
        }

        $this->info("Sent {$alertCount} upcoming expiry alerts.");
        Log::info("CheckBatchExpiry: Sent {$alertCount} upcoming expiry alerts.");
    }
}
