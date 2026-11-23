<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class MeterReadingController extends Controller
{
    public function info(): JsonResponse
    {
        return response()->json([
            'system' => 'Система оплати комунальних послуг',
            'variant' => 15,
            'meter_readings' => 'Приймання показників буде реалізовано в наступних лабораторних роботах.',
        ]);
    }
}
