<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BatchStockLog extends Model
{
    protected $fillable = [
        'product_batch_id',
        'transaction_type',
        'quantity_change',
        'reference_id',
        'reference_type',
        'transaction_date',
        'user_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
