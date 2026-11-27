<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function confirm(Payment $record, User $actor): Payment
    {
        return DB::transaction(function () use ($record, $actor): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($record->getKey());
            Gate::forUser($actor)->authorize('view', $payment);
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
    }

    public function cancel(Payment $record, User $actor): Payment
    {
        return DB::transaction(function () use ($record, $actor): Payment {
            $payment = Payment::query()->lockForUpdate()->findOrFail($record->getKey());
            Gate::forUser($actor)->authorize('view', $payment);
            abort_if($payment->status === 'paid', 409, 'Платіж уже підтверджено.');
            $payment->update(['status' => 'cancelled']);

            return $payment;
        }, 3);
    }

    public function remaining(float $amount, float $paid): float
    {
        if ($amount < 0 || $paid < 0) {
            throw new \InvalidArgumentException('Нарахування й оплата мають бути невід’ємними.');
        }

        return round(max(0, $amount - $paid), 2);
    }

    public function checkAmount(Bill $bill, float|string $amount): int
    {
        $remaining = (int) round($this->debt($bill) * 100);
        if ($bill->status === 'paid' || (int) round((float) $amount * 100) > $remaining) {
            throw ValidationException::withMessages(['amount' => 'Сума перевищує залишок до сплати.']);
        }

        return $remaining;
    }

    public function debt(Bill $bill): float
    {
        $paid = $bill->relationLoaded('payments')
            ? $bill->payments->where('status', 'paid')->sum('amount')
            : $bill->payments()->where('status', 'paid')->sum('amount');

        return $this->remaining((float) $bill->amount, (float) $paid);
    }
}
