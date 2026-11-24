<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Receipt> */
class ReceiptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory()->state(['status' => 'paid', 'paid_at' => now()]),
            'number' => fake()->unique()->numerify('RC-########'),
            'issued_at' => now(),
            'total' => 100,
        ];
    }
}
