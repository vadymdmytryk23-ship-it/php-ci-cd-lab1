<?php

declare(strict_types=1);

function sumNaturalNumbers(array $numbers): int
{
    $sum = 0;

    foreach ($numbers as $number) {
        if (is_int($number) && $number > 0) {
            $sum += $number;
        }
    }

    return $sum;
}

function getLastThreeWords(string $text): array
{
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

    return array_slice($words ?: [], -3);
}

function shuffleWords(string $text): string
{
    $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

    if ($words === false) {
        return '';
    }

    shuffle($words);

    return implode(' ', $words);
}
