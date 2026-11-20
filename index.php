<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';
require_once 'app/Services/EntityService.php';
require_once 'app/Services/DataProcessor.php';

use Faker\Factory;

$faker = Factory::create('uk_UA');
$faker->seed(15);

$billAmounts = [];

for ($i = 0; $i < 10; $i++) {
    $billAmounts[] = $faker->randomFloat(2, 100, 3000);
}

$minThreshold = 1000;
$largeBills = array_filter(
    $billAmounts,
    function (float $amount) use ($minThreshold): bool {
        return $amount > $minThreshold;
    }
);

$calculatedBill = calculateTotal(1250.5, 1384.2, 4.32);

$naturalNumbers = [12, 7, 25, 4, 18];
$numbersSum = sumNaturalNumbers($naturalNumbers);

$text = 'Система зберігає показники лічильників і створює щомісячні нарахування';
$lastWords = getLastThreeWords($text);
$shuffledText = shuffleWords($text);

$exceptionMessage = '';
$finallyMessage = '';

try {
    applyDiscount(1284.50, 150);
} catch (Exception $e) {
    $exceptionMessage = 'Помилка: '.$e->getMessage();
} finally {
    $finallyMessage = 'Спробу розрахунку завершено.';
}

function formatMoney(float $amount): string
{
    return number_format($amount, 2, '.', ' ').' грн';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторна робота №3</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f1f5f9; color: #1f2937; }
        main { max-width: 1050px; margin: 0 auto; padding: 32px 20px 50px; }
        h1 { margin-bottom: 8px; color: #1e3a8a; }
        .subtitle { margin-top: 0; color: #475569; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        section { padding: 20px; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08); }
        h2 { margin-top: 0; font-size: 20px; color: #1d4ed8; }
        ul { padding-left: 22px; }
        li { margin: 7px 0; }
        .result { padding: 10px 12px; border-radius: 8px; background: #eff6ff; }
        .error { color: #b91c1c; background: #fef2f2; }
        a { color: #1d4ed8; font-weight: bold; }
        @media (max-width: 760px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main>
        <h1>Система оплати комунальних послуг</h1>
        <p class="subtitle">Лабораторна робота №3, варіант 15</p>

        <div class="grid">
            <section>
                <h2>Розрахунок нарахування</h2>
                <p>Попередній показник: 1250.5</p>
                <p>Поточний показник: 1384.2</p>
                <p>Тариф: 4.32 грн</p>
                <p class="result"><strong>Сума: <?= formatMoney($calculatedBill) ?></strong></p>
                <a href="/app/Models/EntityArray.php">Переглянути масиви сутностей</a>
            </section>

            <section>
                <h2>Нарахування, створені Faker</h2>
                <p>Усі значення: <?= htmlspecialchars(implode(', ', array_map('formatMoney', $billAmounts))) ?></p>
                <p class="result">Більше <?= formatMoney((float) $minThreshold) ?>: <?= htmlspecialchars(implode(', ', array_map('formatMoney', $largeBills))) ?></p>
            </section>

            <section>
                <h2>Індивідуальні завдання</h2>
                <p>Натуральні числа: <?= htmlspecialchars(implode(', ', $naturalNumbers)) ?></p>
                <p class="result">Сума чисел: <strong><?= $numbersSum ?></strong></p>
                <p>Три останні слова: <strong><?= htmlspecialchars(implode(' ', $lastWords)) ?></strong></p>
                <p>Перемішані слова: <?= htmlspecialchars($shuffledText) ?></p>
            </section>

            <section>
                <h2>Обробка винятку</h2>
                <p>Спроба застосувати знижку 150%.</p>
                <p class="result error"><strong><?= htmlspecialchars($exceptionMessage) ?></strong></p>
                <p><?= htmlspecialchars($finallyMessage) ?></p>
            </section>
        </div>
    </main>
</body>
</html>
