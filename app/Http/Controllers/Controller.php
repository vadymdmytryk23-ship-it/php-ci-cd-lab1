<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'API системи оплати комунальних послуг')]
#[OA\Server(url: '/api/v1', description: 'Сервер застосунку')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', bearerFormat: 'Sanctum', scheme: 'bearer')]
abstract class Controller
{
    use AuthorizesRequests;
}
