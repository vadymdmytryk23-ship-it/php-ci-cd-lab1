<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ServiceProvider;
use App\Models\UtilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UtilityServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => UtilityService::with('serviceProvider')->orderBy('id')->get()]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $provider = ServiceProvider::findOrFail($id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('utility_services')->where('service_provider_id', $id)],
            'unit' => ['required', 'string', 'max:255'],
            'tariff' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'active_from' => ['required', 'date_format:Y-m-d'],
        ]);

        return response()->json(['data' => $provider->utilityServices()->create($data)->load('serviceProvider')], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $service = UtilityService::findOrFail($id);
        $data = $request->validate([
            'tariff' => ['required', 'numeric', 'min:0', 'max:99999999.99', 'decimal:0,2'],
            'active_from' => ['required', 'date_format:Y-m-d'],
        ]);
        $service->update($data);

        return response()->json(['data' => $service->load('serviceProvider')]);
    }
}
