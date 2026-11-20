<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Database;
use App\Core\EntityFactory;
use App\Models\Product;
use App\Tasks\Book;
use App\Tasks\Category;
use App\Tasks\DieselEngine;
use App\Tasks\TurbojetEngine;
use App\Tasks\Worker;
use PHPUnit\Framework\TestCase;

class OopArchitectureTest extends TestCase
{
    public function test_database_returns_single_instance(): void
    {
        ob_start();
        $first = Database::getInstance();
        ob_end_clean();

        $this->assertSame($first, Database::getInstance());
    }

    public function test_factory_creates_exportable_product(): void
    {
        $factory = new EntityFactory;
        $product = $factory->createEntity('product', [
            'id' => 'P-2',
            'createdAt' => '2026-11-20',
            'name' => 'Тестовий товар',
            'price' => 500.0,
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertStringContainsString('Тестовий товар', $product->exportToJson());
    }

    public function test_category_adds_searches_and_removes_book(): void
    {
        $category = new Category(1, 'PHP');
        $book = new Book(10, 'Основи PHP', ['Автор'], 2026);

        $category->addBook($book);
        $this->assertSame($book, $category->searchBook('Основи'));

        $category->removeBook(10);
        $this->assertNull($category->searchBook('Основи'));
    }

    public function test_engine_hierarchy_returns_messages(): void
    {
        $diesel = new DieselEngine('D-1');
        $turbojet = new TurbojetEngine('T-1');

        $this->assertSame('Дизельний двигун D-1 запущено.', $diesel->startEngine());
        $this->assertStringContainsString('турбореактивний режим', $turbojet->startEngine());
    }

    public function test_worker_calculates_salary(): void
    {
        $worker = new Worker(12000, 80, 250);

        $this->assertSame(13000.0, $worker->calculateSalary());
    }
}
