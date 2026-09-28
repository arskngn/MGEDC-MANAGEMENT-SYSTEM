<?php

namespace App\Services;

use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserAppearanceSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class AppearanceSettingService
{
    public const SCHEME_COUNT = 8;

    public const COLOR_COUNT = 6;

    /** @var array<int, array<int, string>> */
    public const SCHEME_PRESETS = [
        1 => ['#1C3ABA', '#1C3ABA', '#1E3A8A', '#1E3A8A', '#1C3ABA', '#1C3ABA'],
        2 => ['#FF6C50', '#40E0D0', '#FF6C50', '#40E0D0', '#FF6C50', '#40E0D0'],
        3 => ['#F15A22', '#0A0E27', '#40E0D0', '#F15A22', '#0A0E27', '#40E0D0'],
        4 => ['#F26633', '#2D6B6D', '#D1AF9C', '#F26633', '#2D6B6D', '#D1AF9C'],
        5 => ['#F15A22', '#004238', '#F15A22', '#000000', '#F15A22', '#000000'],
        6 => ['#3B82F6', '#1E3A8A', '#93C5FD', '#3B82F6', '#1E3A8A', '#93C5FD'], // Blue theme
        7 => ['#10B981', '#064E3B', '#6EE7B7', '#10B981', '#064E3B', '#6EE7B7'], // Green theme
        8 => ['#8B5CF6', '#4C1D95', '#C4B5FD', '#8B5CF6', '#4C1D95', '#C4B5FD'], // Purple theme
    ];

    public function buttonColor(): string
    {
        $user = Auth::user();
        if ($user instanceof User) {
            $personal = UserAppearanceSetting::where('user_id', $user->id)->first();
            if ($personal && $personal->button_color) {
                return $personal->button_color;
            }
        }
        
        $setting = GeneralSetting::first();
        return $setting->button_color ?? '#4634ff';
    }

    public function forSystem(?Request $request = null): array
    {
        return $this->getSettings('sidebar', $request); // Using sidebar columns under the hood for "system"
    }

    public function forWelcome(): array
    {
        return $this->forSystem();
    }

    public function forLogin(?Request $request = null): array
    {
        return $this->forSystem($request);
    }

    public function forSidebar(?Request $request = null): array
    {
        return $this->forSystem($request);
    }

    private function getSettings(string $type, ?Request $request = null): array
    {
        $request = $request ?? request();

        // First, check if user is logged in and has personal settings
        $user = Auth::user();
        if ($user instanceof User) {
            $personal = UserAppearanceSetting::where('user_id', $user->id)->first();
            if ($personal) {
                $schemeField = "{$type}_scheme";
                $colorsField = "{$type}_colors";
                return $this->buildPayload(
                    (int) $personal->$schemeField,
                    $personal->$colorsField
                );
            }
        }

        // Next, check if we have a cookie (for guests or login/welcome pages)
        if ($request->hasCookie('user_login_appearance')) {
            $decoded = json_decode($request->cookie('user_login_appearance'), true);
            if (is_array($decoded) && isset($decoded['scheme'])) {
                return $this->buildPayload(
                    (int) $decoded['scheme'],
                    $decoded['colors'] ?? null
                );
            }
        }

        // Finally, use site defaults
        $setting = GeneralSetting::first();
        $schemeField = "{$type}_scheme";
        $colorsField = "{$type}_colors";

        return $this->buildPayload(
            (int) ($setting->$schemeField ?? 1),
            $setting->$colorsField ?? null
        );
    }

    public function personalForUser(User $user): array
    {
        $record = UserAppearanceSetting::firstOrNew(['user_id' => $user->id]);

        return [
            'system' => $this->buildPayload(
                (int) ($record->sidebar_scheme ?: 1),
                $record->sidebar_colors
            ),
            'button_color' => $record->button_color
        ];
    }

    public function siteDefaults(): array
    {
        $setting = GeneralSetting::first();

        return [
            'system' => $this->buildPayload(
                (int) ($setting->sidebar_scheme ?? 1),
                $setting->sidebar_colors ?? null
            ),
            'button_color' => $setting->button_color
        ];
    }

  /**
     * @param  array<string, mixed>  $validated
     */
    public function savePersonal(User $user, array $validated): void
    {
        UserAppearanceSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'welcome_scheme' => $validated['system_scheme'],
                'welcome_colors' => $validated['system_colors'],
                'login_scheme' => $validated['system_scheme'],
                'login_colors' => $validated['system_colors'],
                'sidebar_scheme' => $validated['system_scheme'],
                'sidebar_colors' => $validated['system_colors'],
                'button_color' => $validated['system_button_color'] ?? null,
            ]
        );

        $this->queueLoginAppearanceCookie(
            (int) $validated['system_scheme'],
            $validated['system_colors']
        );
    }

    public function queueLoginAppearanceCookie(int $scheme, array $colors): void
    {
        Cookie::queue(cookie(
            'user_login_appearance',
            json_encode(['scheme' => $scheme, 'colors' => $colors]),
            60 * 24 * 365,
            '/',
            null,
            false,
            false
        ));
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function saveSiteDefaults(array $validated): void
    {
        $setting = GeneralSetting::first();
        $setting->update([
            'welcome_scheme' => $validated['default_system_scheme'],
            'welcome_colors' => $validated['default_system_colors'],
            'login_scheme' => $validated['default_system_scheme'],
            'login_colors' => $validated['default_system_colors'],
            'sidebar_scheme' => $validated['default_system_scheme'],
            'sidebar_colors' => $validated['default_system_colors'],
            'button_color' => $validated['default_system_button_color'] ?? null,
        ]);
    }

    public function buildPayload(int $scheme, ?array $colors): array
    {
        $scheme = $this->normalizeScheme($scheme);

        return [
            'scheme' => $scheme,
            'colors' => $this->normalizeColors($scheme, $colors),
        ];
    }

    public function normalizeScheme(int $scheme): int
    {
        if ($scheme < 0 || $scheme > self::SCHEME_COUNT) {
            return 1;
        }

        return $scheme;
    }

    /**
     * @param  array<int, string>|null  $colors
     * @return array<int, string>
     */
    public function normalizeColors(int $scheme, ?array $colors): array
    {
        $preset = self::SCHEME_PRESETS[$scheme] ?? self::SCHEME_PRESETS[1];
        $normalized = [];

        for ($i = 0; $i < self::COLOR_COUNT; $i++) {
            $hex = $colors[$i] ?? $preset[$i];
            $hex = strtoupper(ltrim((string) $hex, '#'));
            if (preg_match('/^[0-9A-F]{6}$/', $hex)) {
                $normalized[] = '#'.$hex;
            } else {
                $normalized[] = $preset[$i];
            }
        }

        return $normalized;
    }

    /**
     * @return array{welcome_scheme: int, welcome_colors: array, login_scheme: int, login_colors: array}
     */
    public function validateAppearanceInput(array $input, string $prefix = ''): array
    {
        $schemeKey = $prefix.'scheme';
        $colorsKey = $prefix.'colors';
        $buttonColorKey = $prefix.'button_color';

        $scheme = $this->normalizeScheme((int) ($input[$schemeKey] ?? 1));
        $colors = $input[$colorsKey] ?? null;

        if (is_string($colors)) {
            $colors = json_decode($colors, true);
        }
        
        $result = [
            $prefix.'scheme' => $scheme,
            $prefix.'colors' => $this->normalizeColors($scheme, is_array($colors) ? $colors : null),
        ];
        
        if (isset($input[$buttonColorKey])) {
            $buttonColor = ltrim((string) $input[$buttonColorKey], '#');
            if (preg_match('/^[0-9A-Fa-f]{6}$/', $buttonColor)) {
                $result[$buttonColorKey] = '#' . strtoupper($buttonColor);
            }
        }

        return $result;
    }
}
