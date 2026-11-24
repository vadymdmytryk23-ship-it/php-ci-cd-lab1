<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Payment> */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bill_id' => Bill::factory(),
            'amount' => 100,
            'method' => 'card',
            'status' => 'pending',
            'paid_at' => null,
        ];
    }
}
