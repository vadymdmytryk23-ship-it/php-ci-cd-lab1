<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CatalogRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CatalogController extends Controller
{
    public function __construct(private CatalogRecords $records) {}

    public function index(Request $request): View
    {
        $key = $this->key($request);
        $definition = config('catalog.'.$key);
        $this->authorize('viewAny', new $definition['model']);
        $query = $key === 'utility-accounts' ? $definition['model']::query() : $this->records->query($request, $key);
        $query->with($definition['relations'])->latest('id');
        if ($key === 'users' && $request->filled('email')) {
            $query->where('email', $request->string('email')->trim()->toString());
        }

        return view('catalog.index', [
            'key' => $key, 'definition' => $definition, 'items' => $query->paginate(10)->withQueryString(),
            'canCreate' => $request->user()?->can('create', new $definition['model']),
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $key = $this->key($request);
        $definition = config('catalog.'.$key);
        $item = $definition['model']::with($definition['relations'])->findOrFail($id);
        $this->authorize('view', $item);

        return view('catalog.show', compact('key', 'definition', 'item'));
    }

    public function create(Request $request): View
    {
        $key = $this->key($request);
        $this->authorize('create', new (config('catalog.'.$key.'.model')));

        return $this->form($request, $key);
    }

    public function edit(Request $request, int $id): View
    {
        $key = $this->key($request);
        $item = config('catalog.'.$key.'.model')::findOrFail($id);
        $this->authorize('update', $item);

        return $this->form($request, $key, $item);
    }

    public function store(Request $request): RedirectResponse
    {
        $key = $this->key($request);
        $this->authorize('create', new (config('catalog.'.$key.'.model')));
        $this->records->save($request, $key, $this->records->query($request, $key)->getModel());

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно створено.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $key = $this->key($request);
        $item = config('catalog.'.$key.'.model')::findOrFail($id);
        $this->authorize('update', $item);
        $this->records->save($request, $key, $item);

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно оновлено.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $key = $this->key($request);
        $this->records->delete($request, $key, $id);

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно видалено.');
    }

    public function confirm(Request $request, int $id, PaymentController $payments): RedirectResponse
    {
        $this->records->query($request, 'payments')->findOrFail($id);
        $payments->confirm($id);

        return redirect()->route('web.payments.show', $id)->with('success', 'Оплату підтверджено. Квитанція готова.');
    }

    public function cancel(Request $request, int $id, PaymentController $payments): RedirectResponse
    {
        $this->records->query($request, 'payments')->findOrFail($id);
        $payments->cancel($id);

        return redirect()->route('web.payments.show', $id)->with('success', 'Платіж скасовано.');
    }

    public function download(Request $request, int $id, ReceiptController $receipts): StreamedResponse
    {
        $this->records->query($request, 'receipts')->findOrFail($id);

        return $receipts->download($id);
    }

    private function key(Request $request): string
    {
        return explode('.', $request->route()->getName())[1];
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
                || $name === 'user_id') {
                continue;
            }
            $query = $this->records->query($request, $source)->orderBy('id');
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
}
