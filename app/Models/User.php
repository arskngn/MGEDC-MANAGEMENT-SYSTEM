<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HandlesFileUploads;

/**
 * @property int $id
 * @property int|null $role_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string|null $image
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $last_seen
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Role|null $role
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StaffTarget> $targets
 */
#[Fillable(['name', 'email', 'phone', 'password', 'image', 'status', 'last_seen', 'role_id'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HandlesFileUploads;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_seen' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function targets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StaffTarget::class);
    }

    public function hasPermission($permission): bool
    {
        if (! $this->role_id) {
            return false;
        }

        // Admin role always has full permissions
        if ($this->hasRole('admin')) {
            return true;
        }

        // Cache user permissions list for the current session (1 hour TTL)
        $cacheKey = "user_permissions_{$this->id}";
        
        $permissions = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            if (! $this->role) {
                return [];
            }
            return $this->role->permissions()->pluck('name')->toArray();
        });

        return in_array($permission, $permissions, true);
    }

    public function canAccessAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /** Sidebar / search: show Manage Products when user can open at least one item in that group. */
    public function canSeeManageProductsMenu(): bool
    {
        return $this->canAccessAny(['All Product', 'All Categorys', 'All Brands', 'All Unit']);
    }

    public function hasRole($roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function scopeOfRole($query, string $role)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', $role));
    }

    public function isOnline(): bool
    {
        if (! $this->last_seen) {
            return false;
        }

        // Consider online if active in the last 5 minutes
        return $this->last_seen->gt(now()->subMinutes(5));
    }
}

