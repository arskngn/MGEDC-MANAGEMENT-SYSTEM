<?php

namespace Tests\Unit;

use App\Models\GeneralSetting;
use App\Services\CachedSettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachedSettingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_settings_are_cached_on_first_access(): void
    {
        GeneralSetting::query()->delete();

        $setting = GeneralSetting::factory()->create([
            'currency_symbol' => '$',
            'currency' => 'USD',
        ]);

        Cache::flush();

        $result = CachedSettingService::get();

        $this->assertNotNull($result);
        $this->assertEquals($setting->id, $result->id);

        $cachedResult = CachedSettingService::get();

        $this->assertEquals($result->id, $cachedResult->id);
    }

    public function test_currency_symbol_helper(): void
    {
        GeneralSetting::query()->delete();
        GeneralSetting::factory()->create(['currency_symbol' => '€']);

        Cache::flush();

        $symbol = CachedSettingService::currencySymbol();

        $this->assertEquals('€', $symbol);
    }

    public function test_cache_invalidation_on_update(): void
    {
        GeneralSetting::query()->delete();

        $setting = GeneralSetting::factory()->create(['currency_symbol' => '$']);

        Cache::flush();

        $initial = CachedSettingService::currencySymbol();
        $this->assertEquals('$', $initial);

        $setting->update(['currency_symbol' => '¥']);

        Cache::forget('general_setting_cached');
        Cache::forget('general_setting_currency_symbol');

        $updated = CachedSettingService::currencySymbol();

        $this->assertEquals('¥', $updated);
    }

    public function test_default_values_when_no_setting(): void
    {
        Cache::flush();
        GeneralSetting::query()->delete();

        $symbol = CachedSettingService::currencySymbol();

        $this->assertEquals('$', $symbol);
    }
}
