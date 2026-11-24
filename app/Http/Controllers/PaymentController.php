<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $query = Payment::with(['bill.utilityAccount', 'receipt'])->latest('id');
        if (isset($data['from'])) {
            $query->whereDate('paid_at', '>=', $data['from']);
        }
        if (isset($data['to'])) {
            $query->whereDate('paid_at', '<=', $data['to']);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $bill = Bill::findOrFail($id);
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99', 'decimal:0,2'],
            'method' => ['required', 'in:card,payment_service'],
        ]);

        $this->checkAmount($bill, $data['amount']);
        $payment = $bill->payments()->create($data);

        return response()->json(['data' => $payment->load('bill')], 201);
    }

    public function confirm(int $id): JsonResponse
    {
        $payment = DB::transaction(function () use ($id): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($id);
            if ($payment->status === 'paid') {
                return $payment;
            }
            abort_unless($payment->status === 'pending', 409, 'Платіж уже скасовано.');

            $bill = Bill::query()->lockForUpdate()->findOrFail($payment->bill_id);
            $remaining = $this->checkAmount($bill, $payment->amount);
            $payment->update(['status' => 'paid', 'paid_at' => now()]);
            $payment->receipt()->create([
                'number' => 'RC-'.$payment->id,
                'total' => $payment->amount,
            ]);
            if ((int) round((float) $payment->amount * 100) === $remaining) {
                $bill->update(['status' => 'paid']);
            }

            return $payment;
        }, 3);

        return response()->json(['data' => $payment->load(['bill', 'receipt'])]);
    }

    public function cancel(int $id): JsonResponse
    {
        $payment = Payment::findOrFail($id);
        $changed = Payment::whereKey($id)->where('status', 'pending')->update(['status' => 'cancelled']);
        abort_if($changed === 0 && $payment->refresh()->status !== 'cancelled', 409, 'Платіж уже підтверджено.');

        return response()->json(['data' => $payment->refresh()]);
    }

    private function checkAmount(Bill $bill, float|string $amount): int
    {
        $paid = $bill->payments()->where('status', 'paid')->sum('amount');
        $remaining = (int) round((float) $bill->amount * 100) - (int) round((float) $paid * 100);
        if ($bill->status === 'paid' || (int) round((float) $amount * 100) > $remaining) {
            throw ValidationException::withMessages(['amount' => 'Сума перевищує залишок до сплати.']);
        }

        return $remaining;
    }
}
