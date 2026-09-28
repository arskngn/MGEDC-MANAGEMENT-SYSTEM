<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $user_id
 * @property string $invoice_no
 * @property \Carbon\Carbon $sale_date
 * @property string|null $note
 * @property float $subtotal
 * @property float $discount
 * @property float $receivable_amount
 * @property float $paid_amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|SaleItem[] $items
 * @property-read float $due_amount
 */
class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /* ── Delivery status constants ── */
    const DELIVERY_NOT_DISPATCHED  = 'not_dispatched';
    const DELIVERY_IN_TRUCK        = 'in_truck';
    const DELIVERY_OUT_FOR_DEL     = 'out_for_delivery';
    const DELIVERY_DELIVERED       = 'delivered';
    const DELIVERY_FAILED          = 'failed_delivery';
    const DELIVERY_RETURNED        = 'returned';

    public static array $deliveryStatuses = [
        self::DELIVERY_NOT_DISPATCHED => 'Not Dispatched',
        self::DELIVERY_IN_TRUCK       => 'In Truck',
        self::DELIVERY_OUT_FOR_DEL    => 'Out for Delivery',
        self::DELIVERY_DELIVERED      => 'Delivered',
        self::DELIVERY_FAILED         => 'Failed Delivery',
        self::DELIVERY_RETURNED       => 'Returned',
    ];

    protected $fillable = [
        'customer_id',
        'user_id',
        'salesperson_id',
        'invoice_no',
        'sale_date',
        'payment_status',
        'subtotal',
        'discount',
        'discount_type',
        'receivable_amount',
        'paid_amount',
        'store_category_id',
        'status',
        'delivery_status',
        'delivered_at',
        'delivery_note',
    ];

    protected function casts(): array
    {
        return [
            'sale_date'    => 'date',
            'delivered_at' => 'datetime',
            'subtotal'         => 'decimal:2',
            'discount'         => 'decimal:2',
            'receivable_amount' => 'decimal:2',
            'paid_amount'      => 'decimal:2',
        ];
    }

    /* ── Sale status helpers ── */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeNotCancelled($query)
    {
        $table = $query->getModel()->getTable();
        // Only count sales that have been successfully delivered.
        // Sales that are not yet delivered (not_dispatched, in_truck, out_for_delivery),
        // cancelled/rejected, or failed/returned deliveries are excluded.
        return $query->whereNotIn("{$table}.status", ['cancelled', 'rejected'])
            ->where("{$table}.delivery_status", self::DELIVERY_DELIVERED);
    }

    /* ── Delivery status helpers ── */

    public function isDelivered(): bool
    {
        return $this->delivery_status === self::DELIVERY_DELIVERED;
    }

    public function isInTransit(): bool
    {
        return in_array($this->delivery_status, [
            self::DELIVERY_IN_TRUCK,
            self::DELIVERY_OUT_FOR_DEL,
        ]);
    }

    public function isDispatched(): bool
    {
        return $this->delivery_status !== self::DELIVERY_NOT_DISPATCHED;
    }

    public function getDeliveryStatusLabelAttribute(): string
    {
        return self::$deliveryStatuses[$this->delivery_status] ?? 'Not Dispatched';
    }

    public function getDeliveryStatusColorAttribute(): array
    {
        return match ($this->delivery_status) {
            self::DELIVERY_IN_TRUCK      => [
                'bg'     => 'bg-blue-100 dark:bg-blue-900/30',
                'text'   => 'text-blue-700 dark:text-blue-300',
                'border' => 'border-blue-200 dark:border-blue-700',
            ],
            self::DELIVERY_OUT_FOR_DEL   => [
                'bg'     => 'bg-amber-100 dark:bg-amber-900/30',
                'text'   => 'text-amber-700 dark:text-amber-300',
                'border' => 'border-amber-200 dark:border-amber-700',
            ],
            self::DELIVERY_DELIVERED     => [
                'bg'     => 'bg-green-100 dark:bg-green-900/30',
                'text'   => 'text-green-700 dark:text-green-300',
                'border' => 'border-green-200 dark:border-green-700',
            ],
            self::DELIVERY_FAILED        => [
                'bg'     => 'bg-red-100 dark:bg-red-900/30',
                'text'   => 'text-red-700 dark:text-red-300',
                'border' => 'border-red-200 dark:border-red-700',
            ],
            self::DELIVERY_RETURNED      => [
                'bg'     => 'bg-purple-100 dark:bg-purple-900/30',
                'text'   => 'text-purple-700 dark:text-purple-300',
                'border' => 'border-purple-200 dark:border-purple-700',
            ],
            default                      => [
                'bg'     => 'bg-gray-100 dark:bg-gray-700',
                'text'   => 'text-gray-500 dark:text-gray-400',
                'border' => 'border-gray-200 dark:border-gray-600',
            ],
        };
    }

    public function scopeByDeliveryStatus($query, string $status)
    {
        return $query->where('delivery_status', $status);
    }

    /* ── Relationships ── */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function storeCategory(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getDueAmountAttribute(): float
    {
        $totalReturnReceivable = $this->relationLoaded('saleReturns')
            ? (float) $this->saleReturns->where('status', '!=', SaleReturn::STATUS_REJECTED)->sum('payable_amount')
            : (float) $this->saleReturns()->where('status', '!=', SaleReturn::STATUS_REJECTED)->sum('payable_amount');

        $effectiveReceivable = max(0, (float) $this->receivable_amount - $totalReturnReceivable);

        return max(0, $effectiveReceivable - (float) $this->paid_amount);
    }

    public function hasReturns(): bool
    {
        return $this->saleReturns()->where('status', '!=', SaleReturn::STATUS_REJECTED)->exists();
    }

    public function getTotalItemDiscountsAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(function ($item) {
                if ($item->is_free) return 0;
                $lineGross = (float) $item->quantity * (float) $item->unit_price;
                return max(0, $lineGross - (float) $item->line_total);
            });
        }
        return 0.0;
    }

    public function getTotalDiscountAttribute(): float
    {
        return $this->total_item_discounts + (float) $this->discount;
    }

    public function getGrossSubtotalAttribute(): float
    {
        if ($this->relationLoaded('items')) {
            return (float) $this->items->sum(function ($item) {
                return $item->is_free ? 0 : ((float) $item->quantity * (float) $item->unit_price);
            });
        }
        return (float) $this->subtotal + $this->total_item_discounts;
    }

    public static function nextInvoiceNo(): string
    {
        // Use database-level locking to prevent race conditions
        $max = static::query()
            ->where('invoice_no', 'like', 'S-%')
            ->lockForUpdate()
            ->latest('id')
            ->selectRaw("CAST(SUBSTRING(invoice_no, 3) AS UNSIGNED) as invoice_int")
            ->value('invoice_int') ?? 0;

        return 'S-'.str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }
}

