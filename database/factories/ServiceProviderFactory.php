<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ServiceProvider> */
class ServiceProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'edrpou' => fake()->unique()->numerify('########'),
            'bank_account' => fake()->iban('UA'),
            'contact_email' => fake()->safeEmail(),
        ];
    }
}
