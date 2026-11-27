<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\LogUtilityAccountCreated;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\User;
use App\Models\UtilityAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CatalogService
{
    public function __construct(private readonly PaymentService $payments) {}

    public function query(User $user, string $key): Builder
    {
        $definition = config('catalog.'.$key);
        $query = $definition['model']::query();
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

    public function create(User $user, string $key, array $data): Model
    {
        $record = $this->save($user, $key, new (config('catalog.'.$key.'.model')), $data);
        if ($record instanceof UtilityAccount) {
            LogUtilityAccountCreated::dispatch($record->id, $user->id)->afterCommit();
        }

        return $record;
    }

    public function update(User $user, string $key, Model $record, array $data): Model
    {
        return $this->save($user, $key, $record, $data);
    }

    public function paginate(?User $user, string $key, array $filters): LengthAwarePaginator
    {
        $definition = config('catalog.'.$key);
        $public = in_array($key, ['utility-accounts', 'service-providers', 'utility-services'], true);
        $query = $public ? $definition['model']::query() : $this->query($user, $key);
        $query->when($filters['email'] ?? null, fn (Builder $q, string $email) => $q->where('email', $email))
            ->when($filters['account_id'] ?? null, fn (Builder $q, $id) => $q->where('utility_account_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['from'] ?? null, fn (Builder $q, string $date) => $q->whereDate('paid_at', '>=', $date))
            ->when($filters['to'] ?? null, fn (Builder $q, string $date) => $q->whereDate('paid_at', '<=', $date));

        return $query->with($definition['relations'])->latest('id')->paginate(10)->withQueryString();
    }

    public function statistics(): array
    {
        return [
            'users' => User::count(),
            'unpaid_bills' => Bill::where('status', 'unpaid')->count(),
            'paid' => (float) Payment::where('status', 'paid')->sum('amount'),
        ];
    }

    private function save(User $user, string $key, Model $item, array $data): Model
    {
        return DB::transaction(function () use ($user, $key, $item, $data): Model {
            if ($item->exists) {
                $item = $item->newQuery()->lockForUpdate()->findOrFail($item->id);
                Gate::forUser($user)->authorize('update', $item);
            }
            if ($key === 'utility-accounts') {
                $data['user_id'] = $user->id;
                $ids = $data['service_ids'] ?? [];
                unset($data['service_ids']);
                $item->fill($data)->save();
                $item->utilityServices()->sync($ids);

                return $item;
            }
            if ($key === 'meter-readings' || $key === 'bills') {
                $account = $this->query($user, 'utility-accounts')->findOrFail($data['utility_account_id']);
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
            if ($key === 'utility-services' && $this->query($user, $key)
                ->where('service_provider_id', $data['service_provider_id'])->where('name', $data['name'])
                ->when($item->exists, fn ($q) => $q->whereKeyNot($item->id))->exists()) {
                throw ValidationException::withMessages(['name' => 'Цей постачальник уже має таку послугу.']);
            }
            if ($key === 'payments') {
                $bill = $this->query($user, 'bills')->findOrFail($data['bill_id']);
                if ($item->status === 'paid') {
                    if ($item->bill_id != $bill->id || (float) $item->amount !== (float) $data['amount']) {
                        throw ValidationException::withMessages(['amount' => 'Суму та нарахування підтвердженого платежу змінити неможливо.']);
                    }
                } else {
                    $this->payments->checkAmount($bill, $data['amount']);
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
                if ($item->id === $user->id && ($data['role'] !== 'administrator' || $data['status'] !== 'active')) {
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

    public function delete(User $user, string $key, Model $record): void
    {
        DB::transaction(function () use ($key, $user, $record): void {
            $item = $record->newQuery()->lockForUpdate()->findOrFail($record->getKey());
            Gate::forUser($user)->authorize('delete', $item);
            abort_if($key === 'users' && $item->id === $user->id, 422, 'Власний профіль видалити неможливо.');
            abort_if($key === 'utility-accounts' && $user->role !== 'administrator'
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
