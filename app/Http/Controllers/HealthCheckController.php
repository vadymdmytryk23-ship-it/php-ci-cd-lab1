<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'OK', 'timestamp' => now()]);
    }

    public function info(): JsonResponse
    {
        return response()->json(['system' => 'Система оплати комунальних послуг', 'variant' => 15]);
    }
}
