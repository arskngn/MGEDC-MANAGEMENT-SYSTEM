<?php

namespace Database\Factories;

use App\Models\GeneralSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GeneralSetting>
 */
class GeneralSettingFactory extends Factory
{
    protected $model = GeneralSetting::class;

    public function definition(): array
    {
        return [
            'site_title' => fake()->company(),
            'currency' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'UTC',
            'records_per_page' => 20,
            'currency_format' => 'both',
        ];
    }
}
