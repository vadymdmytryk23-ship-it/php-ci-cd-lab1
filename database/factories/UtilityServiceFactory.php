<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceProvider;
use App\Models\UtilityService;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UtilityService> */
class UtilityServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_provider_id' => ServiceProvider::factory(),
            'name' => fake()->unique()->bothify('Послуга-####??'),
            'unit' => 'м³',
            'tariff' => fake()->randomFloat(2, 1, 60),
            'active_from' => '2026-01-01',
        ];
    }
}
