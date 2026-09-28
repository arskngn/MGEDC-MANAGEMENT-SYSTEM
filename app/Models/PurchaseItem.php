<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $purchase_id
 * @property int $product_id
 * @property string $product_name
 * @property string $sku
 * @property float $quantity
 * @property string|null $unit_label
 * @property float $unit_price
 * @property float $line_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Purchase $purchase
 * @property-read \App\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<ProductBatch> $batches
 */
class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'received_quantity',
        'unit_label',
        'unit_price',
        'unit_discount',
        'unit_discount_type',
        'discount_rate',
        'discount_type',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'received_quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'unit_discount' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class, 'purchase_item_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
