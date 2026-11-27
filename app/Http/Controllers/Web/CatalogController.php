<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\CatalogRoute;
use App\Http\Controllers\Controller;
use App\Http\Requests\CatalogIndexRequest;
use App\Http\Requests\StoreCatalogRequest;
use App\Http\Requests\UpdateCatalogRequest;
use App\Models\Bill;
use App\Models\Payment;
use App\Services\CatalogService;
use App\Services\PaymentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalog, private readonly PaymentService $payments) {}

    public function index(CatalogIndexRequest $request): View
    {
        $key = CatalogRoute::key($request->route());
        $definition = config('catalog.'.$key);
        $model = CatalogRoute::model($key);
        $this->authorize('viewAny', $model);

        return view('catalog.index', [
            'key' => $key,
            'definition' => $definition,
            'items' => $this->catalog->paginate($request->user(), $key, $request->validated()),
            'canCreate' => $request->user()?->can('create', $model),
        ]);
    }

    public function show(Request $request, Model $record): View
    {
        $key = CatalogRoute::key($request->route());
        $definition = config('catalog.'.$key);
        $item = $record->load($definition['relations']);
        $this->authorize('view', $item);

        $debt = $item instanceof Bill ? $this->payments->debt($item) : null;

        return view('catalog.show', compact('key', 'definition', 'item', 'debt'));
    }

    public function create(Request $request): View
    {
        $key = CatalogRoute::key($request->route());
        $this->authorize('create', CatalogRoute::model($key));

        return $this->form($request, $key);
    }

    public function edit(Request $request, Model $record): View
    {
        $this->authorize('update', $record);

        return $this->form($request, CatalogRoute::key($request->route()), $record);
    }

    public function store(StoreCatalogRequest $request): RedirectResponse
    {
        $key = CatalogRoute::key($request->route());
        $this->catalog->create($request->user(), $key, $request->validated());

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно створено.');
    }

    public function update(UpdateCatalogRequest $request, Model $record): RedirectResponse
    {
        $key = CatalogRoute::key($request->route());
        $this->catalog->update($request->user(), $key, $record, $request->validated());

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно оновлено.');
    }

    public function destroy(Request $request, Model $record): RedirectResponse
    {
        $key = CatalogRoute::key($request->route());
        $this->catalog->delete($request->user(), $key, $record);

        return redirect()->route("web.$key.index")->with('success', 'Запис успішно видалено.');
    }

    public function confirm(Request $request, Payment $payment): RedirectResponse
    {
        $this->payments->confirm($payment, $request->user());

        return redirect()->route('web.payments.show', $payment)->with('success', 'Оплату підтверджено. Квитанція готова.');
    }

    public function cancel(Request $request, Payment $payment): RedirectResponse
    {
        $this->payments->cancel($payment, $request->user());

        return redirect()->route('web.payments.show', $payment)->with('success', 'Платіж скасовано.');
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
            $query = $this->catalog->query($request->user(), $source)->orderBy('id');
            if ($source === 'payments') {
                $query->where('status', 'paid')->where(fn ($q) => $q->whereDoesntHave('receipt')->orWhere('id', $item?->payment_id));
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
