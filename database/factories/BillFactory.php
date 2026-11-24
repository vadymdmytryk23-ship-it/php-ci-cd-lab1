<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bill;
use App\Models\UtilityAccount;
use App\Models\UtilityService;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Bill> */
class BillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'utility_account_id' => UtilityAccount::factory(),
            'utility_service_id' => UtilityService::factory(),
            'period' => '2026-11',
            'amount' => fake()->randomFloat(2, 100, 1500),
            'due_date' => '2026-12-20',
            'status' => 'unpaid',
        ];
    }
}
