<?php

declare(strict_types=1);

namespace App\Tasks;

class TurbojetEngine extends DieselEngine
{
    public function startEngine(): string
    {
        return parent::startEngine().' Увімкнено турбореактивний режим.';
    }
}
