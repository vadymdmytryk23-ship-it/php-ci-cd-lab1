<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PaymentService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_calculates_remaining_balance_to_two_decimal_places(): void
    {
        $service = new PaymentService;

        $this->assertSame(89.89, $service->remaining(100.01, 10.12));
        $this->assertSame(0.0, $service->remaining(100, 100));
        $this->assertSame(0.0, $service->remaining(100, 110));
    }

    public function test_negative_payment_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PaymentService)->remaining(120, -1);
    }
}
