<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\UtilityAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'account_id' => ['sometimes', 'integer', 'exists:utility_accounts,id'],
            'status' => ['sometimes', 'in:unpaid,paid'],
        ]);
        $query = Bill::with(['utilityAccount', 'utilityService', 'payments.receipt'])
            ->withSum(['payments as paid_amount' => fn ($query) => $query->where('status', 'paid')], 'amount')
            ->latest('id');

        foreach (['account_id' => 'utility_account_id', 'status' => 'status'] as $filter => $column) {
            if (isset($data[$filter])) {
                $query->where($column, $data[$filter]);
            }
        }

        $bills = $query->get()->each(function (Bill $bill): void {
            $bill->setAttribute('debt', number_format((float) $bill->amount - (float) $bill->paid_amount, 2, '.', ''));
        });

        return response()->json(['data' => $bills]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $account = UtilityAccount::findOrFail($id);
        $data = $request->validate([
            'utility_service_id' => ['required', 'integer', Rule::exists('utility_account_utility_service')->where('utility_account_id', $id)],
            'period' => ['required', 'date_format:Y-m', Rule::unique('bills')->where('utility_account_id', $id)->where('utility_service_id', $request->integer('utility_service_id'))],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'due_date' => ['required', 'date_format:Y-m-d'],
        ]);

        return response()->json(['data' => $account->bills()->create($data)->load('utilityService')], 201);
    }
}
