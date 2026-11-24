<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\UtilityAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeterReadingController extends Controller
{
    public function info(): JsonResponse
    {
        return response()->json([
            'system' => 'Система оплати комунальних послуг',
            'variant' => 15,
        ]);
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => MeterReading::with('utilityAccount')->latest('id')->get()]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $account = UtilityAccount::findOrFail($id);
        $data = $request->validate([
            'previous_value' => ['required', 'numeric', 'min:0', 'max:999999999.999', 'decimal:0,3'],
            'current_value' => ['required', 'numeric', 'gte:previous_value', 'max:999999999.999', 'decimal:0,3'],
        ]);

        $reading = $account->meterReadings()->create($data + ['submitted_at' => now()]);

        return response()->json([
            'data' => $reading->load('utilityAccount'),
            'usage' => round((float) $reading->current_value - (float) $reading->previous_value, 3),
        ], 201);
    }
}
