<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property string $product_name
 * @property string|null $sku
 * @property float $quantity
 * @property string|null $unit_label
 * @property float $unit_price
 * @property float|null $cost_price
 * @property float $line_total
 * @property bool $use_fefo
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\Sale $sale
 * @property-read \App\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<SaleItemBatch> $batches
 */
class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'unit_label',
        'unit_price',
        'unit_discount',
        'unit_discount_type',
        'discount_rate',
        'discount_type',
        'cost_price',
        'line_total',
        'use_fefo',
        'is_free',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'unit_discount' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'cost_price' => 'decimal:4',
            'line_total' => 'decimal:2',
            'use_fefo' => 'boolean',
            'is_free' => 'boolean',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(SaleItemBatch::class);
    }
}
