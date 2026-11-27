<?php

declare(strict_types=1);

namespace App\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;

class CatalogRoute
{
    public static function key(Route $route): string
    {
        $parts = explode('.', (string) $route->getName());

        return $parts[count($parts) - 2];
    }

    public static function model(string $key): Model
    {
        $class = config('catalog.'.$key.'.model');

        return new $class;
    }

    public static function resolve(string $value, Route $route): Model
    {
        return self::model(self::key($route))->newQuery()->findOrFail($value);
    }
}
