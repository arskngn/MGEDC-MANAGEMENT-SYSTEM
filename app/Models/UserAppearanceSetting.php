<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAppearanceSetting extends Model
{
    protected $fillable = [
        'user_id',
        'welcome_scheme',
        'welcome_colors',
        'login_scheme',
        'login_colors',
        'sidebar_scheme',
        'sidebar_colors',
        'button_color',
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
            'button_color' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
