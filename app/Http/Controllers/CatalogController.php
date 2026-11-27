<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $key = $this->key($request);
        $definition = config('catalog.'.$key);
        $query = $this->query($request, $key)->with($definition['relations'])->latest('id');
        if ($key === 'users' && $request->filled('email')) {
            $query->where('email', $request->string('email')->trim()->toString());
        }

        return view('catalog.index', [
            'key' => $key, 'definition' => $definition, 'items' => $query->paginate(10)->withQueryString(),
            'canCreate' => $this->canChange($request, $key, 'create'),
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $key = $this->key($request);
        $definition = config('catalog.'.$key);
        $item = $this->query($request, $key)->with($definition['relations'])->findOrFail($id);

        return view('catalog.show', compact('key', 'definition', 'item'));
    }

    public function create(Request $request): View
    {
        $key = $this->key($request);
        abort_unless($this->canChange($request, $key, 'create'), 403);

        return $this->form($request, $key);
    }

    public function edit(Request $request, int $id): View
    {
        $key = $this->key($request);
        abort_unless($this->canChange($request, $key, 'edit'), 403);

        return $this->form($request, $key, $this->query($request, $key)->findOrFail($id));
    }

    public function store(Request $request): RedirectResponse
    {
        $key = $this->key($request);
        abort_unless($this->canChange($request, $key, 'create'), 403);
        $this->save($request, $key, $this->query($request, $key)->getModel());

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно створено.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $key = $this->key($request);
        abort_unless($this->canChange($request, $key, 'edit'), 403);
        $this->save($request, $key, $this->query($request, $key)->findOrFail($id));

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно оновлено.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $key = $this->key($request);
        abort_unless($this->canChange($request, $key, 'delete'), 403);
        DB::transaction(function () use ($key, $request, $id): void {
            $item = $this->query($request, $key)->lockForUpdate()->findOrFail($id);
            abort_if($key === 'users' && $id === $request->user()->id, 422, 'Власний профіль видалити неможливо.');
            abort_if($key === 'utility-accounts' && $request->user()->role !== 'administrator'
                && $item->bills()->exists(), 422, 'Рахунок із нарахуваннями видаляє адміністратор.');
            $bill = $item instanceof Payment ? $item->bill : null;
            $item->delete();
            if ($bill) {
                $this->updateBillStatus($bill);
            }
        });

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно видалено.');
    }

    public function confirm(Request $request, int $id, PaymentController $payments): RedirectResponse
    {
        $this->query($request, 'payments')->findOrFail($id);
        $payments->confirm($id);

        return redirect()->route('web.payments.show', $id)->with('success', 'Оплату підтверджено. Квитанція готова.');
    }

    public function cancel(Request $request, int $id, PaymentController $payments): RedirectResponse
    {
        $this->query($request, 'payments')->findOrFail($id);
        $payments->cancel($id);

        return redirect()->route('web.payments.show', $id)->with('success', 'Платіж скасовано.');
    }

    public function download(Request $request, int $id, ReceiptController $receipts): StreamedResponse
    {
        $this->query($request, 'receipts')->findOrFail($id);

        return $receipts->download($id);
    }

    private function key(Request $request): string
    {
        return explode('.', $request->route()->getName())[1];
    }

    private function query(Request $request, string $key): Builder
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

    private function canChange(Request $request, string $key, string $action): bool
    {
        return $request->user()->role === 'administrator'
            || in_array($key, ['utility-accounts', 'meter-readings'], true)
            || ($key === 'payments' && $action === 'create');
    }

    private function form(Request $request, string $key, ?Model $item = null): View
    {
        $definition = config('catalog.'.$key);
        if ($key === 'utility-accounts') {
            $item?->load('utilityServices');
        }
        $options = [];
        foreach ($definition['fields'] as $name => $field) {
            $source = $field['options'];
            if (! is_string($source) || $source === 'unique'
                || ($name === 'user_id' && $request->user()->role !== 'administrator')) {
                continue;
            }
            $query = $this->query($request, $source)->orderBy('id');
            if ($source === 'users') {
                $query->where('role', 'customer')->where(fn (Builder $q) => $q->where('status', 'active')->orWhere('id', $item?->user_id));
            }
            if ($source === 'payments') {
                $query->where('status', 'paid')->where(fn (Builder $q) => $q->whereDoesntHave('receipt')->orWhere('id', $item?->payment_id));
            }
            $options[$name] = $query->get()->mapWithKeys(fn (Model $row) => [
                $row->id => match ($source) {
                    'bills' => "#{$row->id} · {$row->period} · {$row->amount} грн",
                    'payments' => "#{$row->id} · {$row->amount} грн",
                    default => $row->name ?? $row->account_number,
                },
            ])->all();
        }

        return view('catalog.form', compact('key', 'definition', 'item', 'options'));
    }

    private function save(Request $request, string $key, Model $item): void
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
            $rules['user_id'] = $request->user()->role === 'administrator'
                ? ['required', Rule::exists('users', 'id')->where('role', 'customer')]
                : ['prohibited'];
        }
        if ($key === 'users' && $item->exists) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'max:72'];
        }
        $messages = ['required' => 'Заповніть це поле.', 'unique' => 'Такий запис уже існує.', 'exists' => 'Оберіть дійсне значення.',
            'min.string' => 'Введіть щонайменше :min символів.', 'numeric' => 'Введіть число.', 'gte' => 'Значення менше за попередній показник.'];
        $data = $request->validate($rules, $messages);

        DB::transaction(function () use ($request, $key, $item, $data): void {
            if ($item->exists) {
                $item = $this->query($request, $key)->lockForUpdate()->findOrFail($item->id);
            }
            if ($key === 'utility-accounts') {
                $data['user_id'] ??= $request->user()->id;
                $ids = $data['service_ids'] ?? [];
                unset($data['service_ids']);
                $item->fill($data)->save();
                $item->utilityServices()->sync($ids);

                return;
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
}
