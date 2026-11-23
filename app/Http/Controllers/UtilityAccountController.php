<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UtilityAccountController extends Controller
{
    private array $accounts = [
        [
            'id' => 1,
            'account_number' => 'UA-150001',
            'address' => 'м. Київ, вул. Хрещатик, 15',
            'provider' => 'Київські енергетичні послуги',
            'service_type' => 'electricity',
        ],
        [
            'id' => 2,
            'account_number' => 'UA-150002',
            'address' => 'м. Київ, вул. Велика Васильківська, 41',
            'provider' => 'Київводоканал',
            'service_type' => 'water',
        ],
        [
            'id' => 3,
            'account_number' => 'UA-150003',
            'address' => 'м. Київ, вул. Антоновича, 20',
            'provider' => 'Київтеплоенерго',
            'service_type' => 'heating',
        ],
    ];

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->accounts,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_number' => ['required', 'string', 'min:6', 'max:30'],
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'provider' => ['required', 'string', 'min:2', 'max:100'],
            'service_type' => ['required', 'string', 'in:electricity,gas,water,heating'],
        ]);

        $newAccount = [
            'id' => count($this->accounts) + 1,
            'account_number' => $validated['account_number'],
            'address' => $validated['address'],
            'provider' => $validated['provider'],
            'service_type' => $validated['service_type'],
        ];

        return response()->json([
            'status' => 'created',
            'data' => $newAccount,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        foreach ($this->accounts as $account) {
            if ($account['id'] === (int) $id) {
                return response()->json([
                    'status' => 'success',
                    'data' => $account,
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Особовий рахунок із зазначеним ідентифікатором не знайдено.',
        ], 404);
    }
}
