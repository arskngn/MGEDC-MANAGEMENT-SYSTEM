<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string|null $address
 * @property int|null $store_category_id
 * @property-read StoreCategory|null $storeCategory
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sale[] $sales
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SaleReturn[] $saleReturns
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['name', 'phone', 'email', 'address', 'credit_limit', 'store_category_id', 'route_id', 'status'];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Customer $customer) {
            if (empty($customer->status)) {
                $customer->status = self::STATUS_ACTIVE;
            }
        });
    }

    public static function getUniqueStoreCategories(): Collection
    {
        return StoreCategory::query()
            ->orderBy('name')
            ->pluck('name');
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    /**
     * @param Builder<Customer> $query
     * @return Builder<Customer>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', '=', self::STATUS_ACTIVE);
    }

    /**
     * @param Builder<Customer> $query
     * @return Builder<Customer>
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', '=', self::STATUS_INACTIVE);
    }

    public function storeCategory(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(CustomerRoute::class, 'route_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function saleReturns(): HasManyThrough
    {
        // Use string class name to keep IDE/linter happy during early module scaffolding.
        return $this->hasManyThrough(SaleReturn::class, Sale::class, 'customer_id', 'sale_id');
    }
}

