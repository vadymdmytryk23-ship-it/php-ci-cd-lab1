<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MeterReading;
use App\Models\UtilityAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MeterReading> */
class MeterReadingFactory extends Factory
{
    public function definition(): array
    {
        $previous = fake()->numberBetween(10, 1000);

        return [
            'utility_account_id' => UtilityAccount::factory(),
            'previous_value' => $previous,
            'current_value' => $previous + fake()->numberBetween(1, 100),
            'submitted_at' => now(),
            'status' => 'submitted',
        ];
    }
}
