<?php

declare(strict_types=1);

$services = [
    'Водопостачання',
    'Електроенергія',
    'Газопостачання',
    'Опалення',
    'Вивезення сміття',
];

sort($services);

$bill = [
    'id' => 15,
    'period' => 'Листопад 2026',
    'amount' => 1284.50,
    'dueDate' => '2026-11-30',
    'status' => 'Не оплачено',
];
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Масиви сутностей</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; color: #1f2937; }
        h1, h2 { color: #1d4ed8; }
        section { padding: 20px; margin-bottom: 20px; border: 1px solid #dbeafe; border-radius: 10px; background: #f8fafc; }
        li { margin: 8px 0; }
        a { color: #1d4ed8; }
    </style>
</head>
<body>
    <h1>Масиви системи комунальних платежів</h1>

    <section>
        <h2>Види комунальних послуг</h2>
        <ul>
            <?php foreach ($services as $service) { ?>
                <li><?= htmlspecialchars($service) ?></li>
            <?php } ?>
        </ul>
    </section>

    <section>
        <h2>Дані нарахування</h2>
        <ul>
            <?php foreach ($bill as $key => $value) { ?>
                <li><strong><?= htmlspecialchars((string) $key) ?>:</strong> <?= htmlspecialchars((string) $value) ?></li>
            <?php } ?>
        </ul>
    </section>

    <a href="/index.php">Повернутися на головну сторінку</a>
</body>
</html>
