@extends('layouts.app')
@section('title', $item ? 'Редагування запису' : 'Додавання запису')

@section('content')
    <a class="back-link" href="{{ route("web.$key.index") }}">← {{ $definition['title'] }}</a>
    <h1>{{ $item ? 'Редагування запису' : 'Додавання запису' }}</h1>
    <form class="sheet editor" method="POST" novalidate
          action="{{ $item ? route("web.$key.update", $item->id) : route("web.$key.store") }}">
        @csrf
        @if ($item)
            @method('PUT')
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">Перевірте поля форми.</div>
        @endif
        @foreach ($definition['fields'] as $name => $field)
            @continue($name === 'user_id')
            @php
                $value = $item?->{$name};
                if (! $item && $key === 'payments' && in_array($name, ['bill_id', 'amount'])) {
                    $value = request()->string($name)->toString();
                }
                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d');
                }
            @endphp
            @if ($field['type'] === 'select')
                <div class="mb-3">
                    <label class="form-label" for="{{ $name }}">{{ $field['label'] }}</label>
                    <select id="{{ $name }}" name="{{ $name }}" @class(['form-select', 'is-invalid' => $errors->has($name)]) required>
                        <option value="">Оберіть значення</option>
                        @foreach ($options[$name] ?? $field['options'] as $id => $label)
                            <option value="{{ $id }}" @selected((string) old($name, $value) === (string) $id)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @elseif ($field['type'] === 'checks')
                <fieldset class="mb-3">
                    <legend class="form-label">{{ $field['label'] }}</legend>
                    <input type="hidden" name="_services_present" value="1">
                    @php($ids = (array) old($name, old('_services_present') ? [] : ($item?->utilityServices->modelKeys() ?? [])))
                    @forelse ($options[$name] as $id => $label)
                        <label class="check-option">
                            <input type="checkbox" name="{{ $name }}[]" value="{{ $id }}" @checked(in_array($id, $ids))>
                            {{ $label }}
                        </label>
                    @empty
                        <p class="muted">Послуг поки немає.</p>
                    @endforelse
                    @error($name)<div class="text-danger">{{ $message }}</div>@enderror
                    @foreach ($errors->get($name.'.*') as $messages)
                        <div class="text-danger">{{ $messages[0] }}</div>
                    @endforeach
                </fieldset>
            @else
                <x-form.input :name="$name" :label="$field['label']" :type="$field['type']" :value="$value"
                    :step="$field['type'] === 'number' ? 'any' : null"
                    :required="! ($item && $name === 'password')" />
            @endif
        @endforeach
        <div class="form-actions">
            <button class="btn btn-primary">{{ $item ? 'Зберегти зміни' : 'Зберегти' }}</button>
            <a class="btn btn-outline-secondary" href="{{ route("web.$key.index") }}">Скасувати</a>
        </div>
    </form>
@endsection
