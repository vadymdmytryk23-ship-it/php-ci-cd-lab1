<?php

declare(strict_types=1);

namespace App\Traits;

use RuntimeException;

trait JsonExportTrait
{
    public function exportToJson(): string
    {
        $json = json_encode(
            get_object_vars($this),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
        );

        if ($json === false) {
            throw new RuntimeException('Не вдалося перетворити об’єкт у JSON.');
        }

        return $json;
    }
}
