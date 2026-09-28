<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnItemBatch extends Model
{
    protected $fillable = [
        'purchase_return_item_id',
        'product_batch_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public function purchaseReturnItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturnItem::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
