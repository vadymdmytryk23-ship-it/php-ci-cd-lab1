<?php

declare(strict_types=1);

namespace App\Tasks;

class Worker
{
    public function __construct(
        public readonly float $baseSalary,
        public readonly int $productCount,
        public readonly float $productPrice,
    ) {}

    public function calculateSalary(): float
    {
        return round(
            $this->baseSalary + ($this->productCount * 0.05 * $this->productPrice),
            2,
        );
    }
}
