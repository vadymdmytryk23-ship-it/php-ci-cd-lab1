<?php

declare(strict_types=1);

namespace App\Tasks;

class DieselEngine extends Engine
{
    public function startEngine(): string
    {
        return "Дизельний двигун {$this->model} запущено.";
    }
}
