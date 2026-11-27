@extends('layouts.app')
@section('title', $definition['title'])

@section('content')
    <a class="back-link" href="{{ route("web.$key.index") }}">← {{ $definition['title'] }}</a>
    <div class="section-heading">
        <h1>Запис №{{ $item->id }}</h1>
        @include('catalog.actions')
    </div>
    <div class="sheet editor">
        <dl class="detail-list">
            @foreach ($definition['fields'] as $name => $field)
                @continue($field['type'] === 'password')
                <dt>{{ $field['label'] }}</dt><dd>@include('catalog.value')</dd>
            @endforeach
            @if (in_array($key, ['bills', 'payments']))
                <dt>Стан</dt><dd>@include('catalog.status')</dd>
            @endif
            @if ($key === 'bills')
                @php($debt = (float) $item->amount - $item->payments->where('status', 'paid')->sum('amount'))
                <dt>Залишок до сплати</dt><dd>{{ number_format($debt, 2, ',', ' ') }} грн</dd>
            @endif
        </dl>
        @if ($key === 'bills' && $debt > 0)
            <a class="btn btn-primary" href="{{ route('web.payments.create', ['bill_id' => $item->id, 'amount' => number_format($debt, 2, '.', '')]) }}">Перейти до оплати</a>
        @elseif ($key === 'payments')
            @if ($item->status === 'pending')
                @error('amount')<p class="alert alert-danger">{{ $message }}</p>@enderror
                <p class="muted">Навчальна оплата. Кошти не списуються.</p>
                <div class="form-actions">
                    @foreach (['confirm' => 'Підтвердити оплату', 'cancel' => 'Скасувати платіж'] as $action => $label)
                        <form method="POST" action="{{ route('web.payments.'.$action, $item->id) }}">
                            @csrf
                            <button @class(['btn', 'btn-primary' => $action === 'confirm', 'btn-outline-secondary' => $action === 'cancel'])>{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            @elseif ($item->receipt)
                <a href="{{ route('web.receipts.download', $item->receipt->id) }}" class="btn btn-primary">Завантажити квитанцію</a>
            @endif
        @elseif ($key === 'receipts')
            <a href="{{ route('web.receipts.download', $item->id) }}" class="btn btn-primary">Завантажити квитанцію</a>
        @endif
    </div>
@endsection
