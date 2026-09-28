<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string|null $name
 * @property string $from_location
 * @property string $to_location
 * @property string|null $description
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Customer[] $customers
 */
class CustomerRoute extends Model
{
    use HasFactory;

    protected $table = 'customer_routes';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'from_location',
        'to_location',
        'description',
        'status',
    ];

    /**
     * Get the formatted display name of the route.
     * E.g. "Main Route (Manila → Quezon City)" or "Manila → Quezon City"
     */
    public function getDisplayNameAttribute(): string
    {
        $path = "{$this->from_location} → {$this->to_location}";
        if (!empty($this->name) && trim($this->name) !== '' && trim($this->name) !== $path) {
            return "{$this->name} ({$path})";
        }

        return $path;
    }

    /**
     * Scope active routes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'route_id');
    }

    public function targets(): HasMany
    {
        return $this->hasMany(StaffTarget::class, 'route_id');
    }
}
