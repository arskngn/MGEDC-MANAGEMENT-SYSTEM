<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseReturnItem;
use App\Models\ProductBatch;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property int $category_id
 * @property int $brand_id
 * @property int $unit_id
 * @property float|string $alert_quantity
 * @property string|null $note
 * @property float $current_stock
 * @property float $total_sold
 * @property \Carbon\Carbon|null $deleted_at
 * @property Category $category
 * @property Brand $brand
 * @property Unit $unit
 * @property \Illuminate\Database\Eloquent\Collection<PurchaseItem> $purchaseItems
 * @property \Illuminate\Database\Eloquent\Collection<SaleItem> $saleItems
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'brand_id',
        'unit_id',
        'alert_quantity',
        'fixed_price',
        'note',
        'current_stock',
        'has_expiry',
        'shelf_life_days',
        'expiry_alert_days',
        'requires_batch_tracking',
    ];

    protected function casts(): array
    {
        return [
            'alert_quantity' => 'decimal:4',
            'current_stock' => 'decimal:4',
            'fixed_price' => 'decimal:2',
            'has_expiry' => 'boolean',
            'requires_batch_tracking' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Get the total available stock from all batches.
     */
    public function getCalculatedStock(): float
    {
        return (float) $this->batches()->where('status', ProductBatch::STATUS_ACTIVE)->sum('quantity_available');
    }

    /**
     * Get the total available stock from all active batches.
     */
    public function getAvailableBatchStock(): float
    {
        return (float) $this->batches()
            ->where('status', ProductBatch::STATUS_ACTIVE)
            ->sum('quantity_available');
    }

    /**
     * Recalculate and update the current_stock column from batches.
     */
    public function updateStock(): void
    {
        $this->update([
            'current_stock' => $this->getCalculatedStock()
        ]);
    }

    /**
     * Placeholder until sales lines exist.
     */
    public function getTotalSoldAttribute(): float
    {
        return (float) SaleItem::query()
            ->where('product_id', '=', $this->id)
            ->sum('quantity');
    }
}
