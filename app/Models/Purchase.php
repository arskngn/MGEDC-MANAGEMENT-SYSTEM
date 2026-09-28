<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $invoice_no
 * @property int $supplier_id
 * @property \Carbon\Carbon $purchase_date
 * @property string|null $note
 * @property float $subtotal
 * @property float $discount
 * @property float $payable_amount
 * @property float $paid_amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Supplier $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchasePayment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseReturn[] $purchaseReturns
 * @property-read float $due_amount
 */
class Purchase extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_no',
        'supplier_id',
        'purchase_date',
        'delivery_date',
        'note',
        'subtotal',
        'discount',
        'discount_type',
        'tax_rate',
        'tax_amount',
        'payable_amount',
        'paid_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'payable_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function getDueAmountAttribute(): float
    {
        $totalReturnReceivable = $this->relationLoaded('purchaseReturns')
            ? (float) $this->purchaseReturns->sum('receivable_amount')
            : (float) $this->purchaseReturns()->sum('receivable_amount');

        $effectivePayable = max(0, (float) $this->payable_amount - $totalReturnReceivable);

        return max(0, $effectivePayable - (float) $this->paid_amount);
    }

    public function hasReturns(): bool
    {
        return $this->purchaseReturns()->exists();
    }

    public function hasReceivedItems(): bool
    {
        if ($this->relationLoaded('items')) {
            return $this->items->contains(fn (PurchaseItem $item) => (float) $item->received_quantity > 0);
        }

        return $this->items()->where('received_quantity', '>', 0)->exists();
    }

    /**
     * Why the purchase order lines cannot be edited (null = editable).
     */
    public function orderEditLockReason(): ?string
    {
        if ($this->hasReturns()) {
            return 'returns';
        }

        if ($this->hasReceivedItems()) {
            return 'received';
        }

        return null;
    }

    public function isOrderEditable(): bool
    {
        return $this->orderEditLockReason() === null;
    }

    public static function nextInvoiceNo(): string
    {
        // Use database-level locking to prevent race conditions
        $max = static::query()
            ->where('invoice_no', 'like', 'P-%')
            ->lockForUpdate()
            ->latest('id')
            ->selectRaw("CAST(SUBSTRING(invoice_no, 3) AS UNSIGNED) as invoice_int")
            ->value('invoice_int') ?? 0;

        return 'P-'.str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }
}
