<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../app/Services/EntityService.php';

final class EntityServiceTest extends TestCase
{
    public function test_bill_calculation(): void
    {
        $this->assertSame(577.58, calculateTotal(1250.5, 1384.2, 4.32));
    }

    public function test_discount_calculation(): void
    {
        $this->assertSame(900.0, applyDiscount(1000, 10));
    }

    public function test_invalid_discount_throws_exception(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Неприпустиме значення знижки!');

        applyDiscount(1000, 150);
    }
}
