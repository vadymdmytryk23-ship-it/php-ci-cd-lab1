<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UtilityAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UtilityAccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = UtilityAccount::with(['user', 'utilityServices.serviceProvider'])->orderBy('id')->get();

        return response()->json(['status' => 'success', 'data' => $accounts]);
    }

    public function show(int $id): JsonResponse
    {
        $account = UtilityAccount::with(['user', 'utilityServices.serviceProvider', 'bills.utilityService', 'meterReadings'])->findOrFail($id);

        return response()->json(['status' => 'success', 'data' => $account]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateAccount($request);

        $account = DB::transaction(function () use ($data): UtilityAccount {
            $account = UtilityAccount::create($data);
            $account->utilityServices()->sync($data['service_ids']);

            return $account;
        });

        return response()->json(['status' => 'created', 'data' => $account->load(['user', 'utilityServices.serviceProvider'])], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $account = UtilityAccount::findOrFail($id);
        $data = $this->validateAccount($request, $account);

        DB::transaction(function () use ($account, $data): void {
            $account->update($data);
            if (array_key_exists('service_ids', $data)) {
                $account->utilityServices()->sync($data['service_ids']);
            }
        });

        return response()->json(['status' => 'success', 'data' => $account->load(['user', 'utilityServices.serviceProvider'])]);
    }

    private function validateAccount(Request $request, ?UtilityAccount $account = null): array
    {
        $presence = $account ? 'sometimes' : 'required';
        $number = Rule::unique('utility_accounts', 'account_number');
        if ($account) {
            $number->ignore($account->id);
        }

        return $request->validate([
            'user_id' => [$presence, 'required', 'integer', Rule::exists('users', 'id')->where('role', 'customer')->where('status', 'active')],
            'account_number' => [$presence, 'required', 'string', 'min:6', 'max:30', $number],
            'address' => [$presence, 'required', 'string', 'min:5', 'max:255'],
            'service_ids' => [$presence, 'array', 'list'],
            'service_ids.*' => ['required', 'integer', 'distinct', 'exists:utility_services,id'],
        ]);
    }
}
