<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Service to manage permission cache invalidation for users
 */
class PermissionCacheService
{
    /**
     * Invalidate all permissions cache for a user
     */
    public static function invalidateUserPermissions(int $userId): void
    {
        Cache::forget("user_permissions_{$userId}");
    }

    /**
     * Invalidate permission cache for all users belonging to a role
     */
    public static function invalidateRolePermissions(int $roleId): void
    {
        $userIds = User::where('role_id', $roleId)->pluck('id');
        foreach ($userIds as $userId) {
            self::invalidateUserPermissions($userId);
        }
    }

    /**
     * Invalidate all permission caches
     */
    public static function invalidateAll(): void
    {
        $userIds = User::pluck('id');
        foreach ($userIds as $userId) {
            self::invalidateUserPermissions($userId);
        }
    }
}
