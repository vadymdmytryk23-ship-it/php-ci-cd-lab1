<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Core\Database;
use App\Core\EntityFactory;
use App\Services\ConsoleLogger;
use App\Services\EntityService;
use App\Tasks\Book;
use App\Tasks\Category;
use App\Tasks\DieselEngine;
use App\Tasks\TurbojetEngine;
use App\Tasks\Worker;

ob_start();
$databaseOne = Database::getInstance();
$databaseMessage = trim((string) ob_get_clean());
$databaseTwo = Database::getInstance();
$sameDatabase = $databaseOne === $databaseTwo;

$factory = new EntityFactory;
$product = $factory->createEntity('product', [
    'id' => 'P-15',
    'createdAt' => '2026-11-20',
    'name' => 'Лічильник електроенергії',
    'price' => 1450.50,
]);
$user = $factory->createEntity('user', [
    'id' => 'U-15',
    'createdAt' => '2026-11-20',
    'name' => 'Вадим Дмитрик',
    'email' => 'student@example.com',
]);

$productJson = $product->exportToJson();

$service = new EntityService(new ConsoleLogger);
ob_start();
$productDetails = $service->process($product);
$logMessage = trim((string) ob_get_clean());

$category = new Category(15, 'Програмування');
$category->addBook(new Book(1, 'Чистий код', ['Роберт Мартін'], 2008));
$category->addBook(new Book(2, 'PHP 8 для розробників', ['Іван Петренко'], 2024));
$foundBook = $category->searchBook('Чистий код');
$category->removeBook(2);
$removedBook = $category->searchBook('PHP 8');

$dieselEngine = new DieselEngine('D-200');
$turbojetEngine = new TurbojetEngine('TJ-15');

$worker = new Worker(12000, 80, 250);
$salary = $worker->calculateSalary();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторна робота №4</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; color: #1f2937; background: #eef2f7; }
        main { max-width: 1100px; margin: 0 auto; padding: 30px 18px 48px; }
        h1 { margin-bottom: 6px; color: #1e3a8a; }
        .subtitle { margin-top: 0; color: #475569; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        section { padding: 20px; border-radius: 10px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08); }
        h2 { margin-top: 0; font-size: 20px; color: #1d4ed8; }
        p { line-height: 1.45; }
        .result { padding: 10px 12px; border-radius: 7px; background: #eff6ff; }
        pre { overflow-x: auto; padding: 12px; color: #e2e8f0; background: #0f172a; border-radius: 7px; }
        @media (max-width: 760px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main>
        <h1>ООП архітектура системи комунальних послуг</h1>
        <p class="subtitle">Лабораторна робота №4, варіант 15</p>

        <div class="grid">
            <section>
                <h2>Singleton і Factory</h2>
                <p class="result"><?= e($databaseMessage) ?></p>
                <p>Повторний виклик повернув той самий об'єкт: <strong><?= $sameDatabase ? 'так' : 'ні' ?></strong></p>
                <p><?= e($productDetails) ?></p>
                <p><?= e($user->getDetails()) ?></p>
            </section>

            <section>
                <h2>Трейт і Dependency Injection</h2>
                <p class="result"><?= e($logMessage) ?></p>
                <pre><?= e($productJson) ?></pre>
            </section>

            <section>
                <h2>Композиція Book і Category</h2>
                <p>Категорія: <strong><?= e($category->name) ?></strong></p>
                <p>Знайдено книгу: <strong><?= e($foundBook?->title ?? 'не знайдено') ?></strong></p>
                <p>Книгу «PHP 8 для розробників» видалено: <strong><?= $removedBook === null ? 'так' : 'ні' ?></strong></p>
            </section>

            <section>
                <h2>Ієрархія двигунів</h2>
                <p><?= e($dieselEngine->startEngine()) ?></p>
                <p class="result"><?= e($turbojetEngine->startEngine()) ?></p>
            </section>

            <section>
                <h2>Розрахунок зарплати Worker</h2>
                <p>Базова ставка: <?= number_format($worker->baseSalary, 2, '.', ' ') ?> грн</p>
                <p>Кількість продукції: <?= $worker->productCount ?></p>
                <p>Ціна одиниці: <?= number_format($worker->productPrice, 2, '.', ' ') ?> грн</p>
                <p class="result"><strong>Зарплата: <?= number_format($salary, 2, '.', ' ') ?> грн</strong></p>
            </section>
        </div>
    </main>
</body>
</html>
