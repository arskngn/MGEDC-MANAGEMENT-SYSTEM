<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OverallQuota extends Model
{
    protected $fillable = [
        'name',
        'target_amount',
        'start_date',
        'end_date',
        'commission_per_staff',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'commission_per_staff' => 'decimal:2',
    ];

    /**
     * Ensure table exists in database even before artisan migrate runs.
     */
    public static function ensureTableExists(): void
    {
        if (!Schema::hasTable('overall_quotas')) {
            Schema::create('overall_quotas', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->decimal('target_amount', 15, 2);
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('commission_per_staff', 10, 2)->default(5000.00);
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['start_date', 'end_date']);
            });
        }
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the total team sales achieved the quota.
     */
    public function isAchieved(float $totalSales): bool
    {
        return (float) $this->target_amount > 0 && $totalSales >= (float) $this->target_amount;
    }

    /**
     * Calculate progress percentage towards the target.
     */
    public function progressPercentage(float $totalSales): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0.0;
        }

        return min(100.0, round(($totalSales / (float) $this->target_amount) * 100, 2));
    }

    /**
     * Remaining sales required to reach target.
     */
    public function remainingAmount(float $totalSales): float
    {
        return max(0.0, (float) $this->target_amount - $totalSales);
    }
}
