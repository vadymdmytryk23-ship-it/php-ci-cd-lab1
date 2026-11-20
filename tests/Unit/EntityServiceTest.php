<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Interfaces\LoggerInterface;
use App\Models\Product;
use App\Services\EntityService;
use PHPUnit\Framework\TestCase;

class EntityServiceTest extends TestCase
{
    public function test_service_uses_injected_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('Оброблено сутність P-1');

        $product = new Product('P-1', '2026-11-20', 'Лічильник', 1200);
        $service = new EntityService($logger);

        $this->assertSame('Товар: Лічильник, ціна: 1200 грн', $service->process($product));
    }
}
