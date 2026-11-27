<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CatalogRecords
{
    public function query(Request $request, string $key): Builder
    {
        $definition = config('catalog.'.$key);
        $query = $definition['model']::query();
        $user = $request->user();
        if ($user->role === 'administrator') {
            return $query;
        }
        if ($key === 'users') {
            abort(403);
        }
        if ($key === 'utility-accounts') {
            return $query->where('user_id', $user->id);
        }
        if ($definition['owner']) {
            $query->whereRelation($definition['owner'], 'user_id', $user->id);
        }

        return $query;
    }

    public function save(Request $request, string $key, Model $item): Model
    {
        $rules = [];
        foreach (config('catalog.'.$key.'.fields') as $name => $field) {
            $rules[$name] = explode('|', $field['rules']);
            if ($field['options'] === 'unique') {
                $rules[$name][] = Rule::unique($item->getTable(), $name)->ignore($item->id);
            }
        }
        if ($key === 'utility-accounts') {
            $rules['service_ids.*'] = ['integer', 'distinct', 'exists:utility_services,id'];
            $rules['user_id'] = ['prohibited'];
        }
        if ($key === 'users' && $item->exists) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'max:72'];
        }
        $messages = ['required' => 'Заповніть це поле.', 'unique' => 'Такий запис уже існує.', 'exists' => 'Оберіть дійсне значення.',
            'min.string' => 'Введіть щонайменше :min символів.', 'numeric' => 'Введіть число.', 'gte' => 'Значення менше за попередній показник.'];
        $data = $request->validate($rules, $messages);

        return DB::transaction(function () use ($request, $key, $item, $data): Model {
            if ($item->exists) {
                $item = $item->newQuery()->lockForUpdate()->findOrFail($item->id);
                Gate::authorize('update', $item);
            }
            if ($key === 'utility-accounts') {
                $data['user_id'] = $request->user()->id;
                $ids = $data['service_ids'] ?? [];
                unset($data['service_ids']);
                $item->fill($data)->save();
                $item->utilityServices()->sync($ids);

                return $item;
            }
            if ($key === 'meter-readings' || $key === 'bills') {
                $account = $this->query($request, 'utility-accounts')->findOrFail($data['utility_account_id']);
            }
            if ($key === 'bills') {
                $pair = ['utility_account_id' => $account->id, 'utility_service_id' => $data['utility_service_id'], 'period' => $data['period']];
                if (! $account->utilityServices()->whereKey($data['utility_service_id'])->exists()
                    && (! $item->exists || $item->utility_account_id != $account->id || $item->utility_service_id != $data['utility_service_id'])) {
                    throw ValidationException::withMessages(['utility_service_id' => 'Цю послугу не підключено до рахунку.']);
                }
                if (Bill::where($pair)->when($item->exists, fn ($q) => $q->whereKeyNot($item->id))->exists()) {
                    throw ValidationException::withMessages(['period' => 'Таке нарахування за цей місяць уже є.']);
                }
                if ($item->exists && $this->paid($item) > (float) $data['amount']) {
                    throw ValidationException::withMessages(['amount' => 'Сума менша за вже сплачену.']);
                }
            }
            if ($key === 'utility-services' && $this->query($request, $key)
                ->where('service_provider_id', $data['service_provider_id'])->where('name', $data['name'])
                ->when($item->exists, fn ($q) => $q->whereKeyNot($item->id))->exists()) {
                throw ValidationException::withMessages(['name' => 'Цей постачальник уже має таку послугу.']);
            }
            if ($key === 'payments') {
                $bill = $this->query($request, 'bills')->findOrFail($data['bill_id']);
                if ($item->status === 'paid') {
                    if ($item->bill_id != $bill->id || (float) $item->amount !== (float) $data['amount']) {
                        throw ValidationException::withMessages(['amount' => 'Суму та нарахування підтвердженого платежу змінити неможливо.']);
                    }
                } elseif (round((float) $bill->amount - $this->paid($bill), 2) < (float) $data['amount']) {
                    throw ValidationException::withMessages(['amount' => 'Сума перевищує залишок до сплати.']);
                }
            }
            if ($key === 'receipts') {
                $payment = Payment::where('status', 'paid')->findOrFail($data['payment_id']);
                if ($payment->receipt()->when($item->exists, fn ($q) => $q->whereKeyNot($item->id))->exists()) {
                    throw ValidationException::withMessages(['payment_id' => 'Для платежу вже є квитанція.']);
                }
                $data['total'] = $payment->amount;
            }
            if ($key === 'users') {
                if ($item->id === $request->user()->id && ($data['role'] !== 'administrator' || $data['status'] !== 'active')) {
                    throw ValidationException::withMessages(['status' => 'Власний профіль адміністратора має залишатися активним.']);
                }
                if (empty($data['password'])) {
                    unset($data['password']);
                }
            }
            $item->forceFill($data)->save();
            if ($item instanceof Bill) {
                $this->updateBillStatus($item);
            }
            if ($key === 'users' && $item->status === 'blocked') {
                $item->tokens()->delete();
            }

            return $item;
        });
    }

    private function paid(Bill $bill): float
    {
        return (float) $bill->payments()->where('status', 'paid')->sum('amount');
    }

    private function updateBillStatus(Bill $bill): void
    {
        $bill->update(['status' => round($this->paid($bill), 2) >= (float) $bill->amount ? 'paid' : 'unpaid']);
    }

    public function delete(Request $request, string $key, int $id): void
    {
        DB::transaction(function () use ($key, $request, $id): void {
            $model = config('catalog.'.$key.'.model');
            $item = $model::query()->lockForUpdate()->findOrFail($id);
            Gate::authorize('delete', $item);
            abort_if($key === 'users' && $id === $request->user()->id, 422, 'Власний профіль видалити неможливо.');
            abort_if($key === 'utility-accounts' && $request->user()->role !== 'administrator'
                && $item->bills()->exists(), 422, 'Рахунок із нарахуваннями видаляє адміністратор.');
            $bill = $item instanceof Payment ? $item->bill : null;
            if ($key === 'users') {
                $item->tokens()->delete();
            }
            $item->delete();
            if ($bill) {
                $this->updateBillStatus($bill);
            }
        });
    }
}
