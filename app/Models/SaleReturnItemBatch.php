<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItemBatch extends Model
{
    public const CONDITION_GOOD = 'good';

    public const CONDITION_DAMAGED = 'damaged';

    protected $fillable = [
        'sale_return_item_id',
        'product_batch_id',
        'quantity',
        'condition',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public static function conditionOptions(): array
    {
        return [
            self::CONDITION_GOOD => 'Good (restock)',
            self::CONDITION_DAMAGED => 'Damaged',
        ];
    }

    public function saleReturnItem(): BelongsTo
    {
        return $this->belongsTo(SaleReturnItem::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
