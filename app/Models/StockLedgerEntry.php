<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_batch_id',
        'reference_type',
        'reference_id',
        'transaction_type',
        'quantity_change',
        'running_balance',
        'notes',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
