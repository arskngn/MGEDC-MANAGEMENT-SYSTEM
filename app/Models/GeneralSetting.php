<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * @property string|null $logo_light
 * @property string|null $logo_dark
 * @property string|null $site_title
 */
class GeneralSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_title',
        'whole_company_name',
        'currency',
        'currency_symbol',
        'timezone',
        'records_per_page',
        'currency_format',
        'logo_light',
        'logo_dark',
        'favicon',
        'welcome_scheme',
        'welcome_colors',
        'login_scheme',
        'login_colors',
        'sidebar_scheme',
        'sidebar_colors',
        'email_notification',
        'sms_notification',
        'notification_retention_days',
        'default_theme',
    ];

    protected function casts(): array
    {
        return [
            'welcome_scheme' => 'integer',
            'welcome_colors' => 'array',
            'login_scheme' => 'integer',
            'login_colors' => 'array',
            'sidebar_scheme' => 'integer',
            'sidebar_colors' => 'array',
            'email_notification' => 'boolean',
            'sms_notification' => 'boolean',
        ];
    }

    /**
     * Get logo_light with fallback to default image
     */
    public function getLogoLightAttribute(?string $value): string
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/logo_light_1774081740.jpg';
    }

    /**
     * Get logo_dark with fallback to default image
     */
    public function getLogoDarkAttribute(?string $value): string
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/logo_dark_1774081633.jpg';
    }

    /**
     * Get favicon with fallback to default image
     */
    public function getFaviconAttribute(?string $value): string
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/favicon_1774081633.jpg';
    }

    /**
     * Check if logo_light is custom (uploaded) vs default
     */
    public function hasCustomLogoLight(): bool
    {
        return ($this->attributes['logo_light'] ?? null) !== null;
    }

    /**
     * Check if logo_dark is custom (uploaded) vs default
     */
    public function hasCustomLogoDark(): bool
    {
        return ($this->attributes['logo_dark'] ?? null) !== null;
    }

    /**
     * Check if favicon is custom (uploaded) vs default
     */
    public function hasCustomFavicon(): bool
    {
        return ($this->attributes['favicon'] ?? null) !== null;
    }

    /**
     * Get the light logo path or default
     */
    public function logoLight(): string
    {
        return $this->getLogoLightAttribute($this->attributes['logo_light'] ?? null);
    }

    /**
     * Get the dark logo path or default
     */
    public function logoDark(): string
    {
        return $this->getLogoDarkAttribute($this->attributes['logo_dark'] ?? null);
    }

    /**
     * Boot method to handle model events
     */
    protected static function booted(): void
    {
        static::saved(function () {
            \App\Services\CachedSettingService::invalidate();
        });

        static::deleted(function () {
            \App\Services\CachedSettingService::invalidate();
        });
    }
}
