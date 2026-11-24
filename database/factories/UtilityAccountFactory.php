<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UtilityAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UtilityAccount> */
class UtilityAccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_number' => fake()->unique()->numerify('UA-#########'),
            'address' => fake()->streetAddress(),
            'status' => 'active',
        ];
    }
}
