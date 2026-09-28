<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\SystemNotification;
use App\Services\NotificationService;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $purchase_id
 * @property int|null $purchase_item_id
 * @property string $batch_number
 * @property string $status
 * @property float $quantity
 * @property float $quantity_available
 * @property float $quantity_sold
 * @property float $quantity_returned
 * @property float $quantity_adjusted
 * @property int $quantity_expired
 * @property Carbon|null $expiry_date
 * @property string|null $manufactured_date
 * @property string|null $received_date
 * @property int $alert_days_before_expiry
 * @property-read Product $product
 * @property-read Purchase|null $purchase
 * @property-read \Illuminate\Database\Eloquent\Collection<SaleItemBatch> $saleItemBatches
 * @property-read \Illuminate\Database\Eloquent\Collection<BatchStockLog> $stockLogs
 */
class ProductBatch extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_DEPLETED = 'depleted';
    const STATUS_HOLD = 'hold';
    const STATUS_QUARANTINED = 'quarantined';

    protected $fillable = [
        'product_id',
        'purchase_id',
        'purchase_item_id',
        'batch_number',
        'status',
        'quantity',
        'quantity_available',
        'quantity_sold',
        'quantity_returned',
        'quantity_adjusted',
        'quantity_expired',
        'expiry_date',
        'manufactured_date',
        'received_date',
        'alert_days_before_expiry',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'quantity_available' => 'float',
            'quantity_sold' => 'float',
            'quantity_returned' => 'float',
            'quantity_adjusted' => 'float',
            'expiry_date' => 'date',
            'manufactured_date' => 'date',
            'received_date' => 'date',
            'quantity_expired' => 'integer',
            'alert_days_before_expiry' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductBatch $batch) {
            if (empty($batch->status)) {
                $batch->status = self::STATUS_ACTIVE;
            }
        });

        static::saving(function (ProductBatch $batch) {
            $batch->updateStatusAutomatically();
        });

        static::saved(function (ProductBatch $batch) {
            // Auto-check expiry after every create/update
            $batch->load('product');
            $batch->checkExpiryAndNotify();
        });
    }

    /**
     * Check if this batch is expiring soon and send a notification if needed.
     * Deduplicates: will not send the same notification twice on the same day.
     */
    public function checkExpiryAndNotify(): void
    {
        if (!$this->expiry_date || $this->status !== self::STATUS_ACTIVE) {
            return;
        }

        $today = Carbon::now()->startOfDay();
        $expiryDate = Carbon::parse($this->expiry_date)->startOfDay();

        if ($expiryDate->lte($today)) {
            return; // Already expired — expiry command handles this
        }

        $daysLeft = $today->diffInDays($expiryDate);
        $alertDays = $this->alert_days_before_expiry
            ?? $this->product?->expiry_alert_days
            ?? 30;

        if ($daysLeft > $alertDays) {
            return; // Not within alert window yet
        }

        $staffToNotify = User::whereHas('role.permissions', function ($q) {
            $q->where('name', 'All Product');
        })->get();

        $title = 'Batch Expiring Soon';
        $message = "Batch #{$this->batch_number} for product '{$this->product->name}' will expire in {$daysLeft} day(s).";
        $notificationService = app(NotificationService::class);

        foreach ($staffToNotify as $staff) {
            $alreadySent = SystemNotification::where('user_id', $staff->id)
                ->where('title', $title)
                ->where('message', $message)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if (!$alreadySent) {
                $notificationService->sendToAdmin($title, $message, 'warning', $staff->id);
            }
        }
    }

    public function updateStatusAutomatically(): void
    {
        if ($this->status === self::STATUS_HOLD || $this->status === self::STATUS_QUARANTINED) {
            return;
        }

        if ($this->quantity_available <= 0) {
            $this->status = self::STATUS_DEPLETED;
            return;
        }

        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @param Builder<ProductBatch> $query
     * @return Builder<ProductBatch>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '=', self::STATUS_ACTIVE);
    }

    /**
     * @param Builder<ProductBatch> $query
     * @return Builder<ProductBatch>
     */
    public function scopeDepleted(Builder $query): Builder
    {
        return $query->where('status', '=', self::STATUS_DEPLETED);
    }

    /**
     * @param Builder<ProductBatch> $query
     * @return Builder<ProductBatch>
     */
    public function scopeHold(Builder $query): Builder
    {
        return $query->where('status', '=', self::STATUS_HOLD);
    }

    /**
     * @param Builder<ProductBatch> $query
     * @return Builder<ProductBatch>
     */
    public function scopeQuarantined(Builder $query): Builder
    {
        return $query->where('status', '=', self::STATUS_QUARANTINED);
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_HOLD => 'On Hold',
            self::STATUS_QUARANTINED => 'Quarantined',
            self::STATUS_DEPLETED => 'Depleted',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function saleItemBatches(): HasMany
    {
        return $this->hasMany(SaleItemBatch::class);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(BatchStockLog::class);
    }

    public function stockLedgerEntries(): HasMany
    {
        return $this->hasMany(StockLedgerEntry::class);
    }

    public function percentageUsed(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        return ($this->quantity_sold / $this->quantity) * 100;
    }
}
