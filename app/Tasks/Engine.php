<?php

declare(strict_types=1);

namespace App\Tasks;

abstract class Engine
{
    public function __construct(protected string $model) {}

    abstract public function startEngine(): string;

    public function getModel(): string
    {
        return $this->model;
    }
}
