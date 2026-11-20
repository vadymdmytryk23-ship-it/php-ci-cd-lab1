<?php

declare(strict_types=1);

function calculateTotal(float $previousReading, float $currentReading, float $tariff): float
{
    $consumption = $currentReading - $previousReading;

    return round($consumption * $tariff, 2);
}

function applyDiscount(float $price, float $discount): float
{
    if ($discount < 0 || $discount > 100) {
        throw new Exception('Неприпустиме значення знижки!');
    }

    return round($price - ($price * ($discount / 100)), 2);
}
