<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\LoggerInterface;
use App\Models\AbstractEntity;

class EntityService
{
    public function __construct(private LoggerInterface $logger) {}

    public function process(AbstractEntity $entity): string
    {
        $this->logger->log('Оброблено сутність '.$entity->getId());

        return $entity->getDetails();
    }
}
