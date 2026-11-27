@php($value = data_get($item, $field['display'] ?? $name))
@if ($key === 'utility-accounts' && $field['type'] !== 'checks' && ! auth()->user()?->can('viewPrivate', $item))
    <span class="muted">Приховано</span>
@elseif ($field['type'] === 'checks')
    @forelse ($value as $service)
        <span class="service-chip" title="{{ $service->serviceProvider->name }}">{{ $service->name }}</span>
    @empty
        <span class="muted">Без послуг</span>
    @endforelse
@elseif ($value instanceof \DateTimeInterface)
    {{ $value->format('d.m.Y') }}
@elseif (is_array($field['options']))
    {{ $field['options'][$value] ?? $value }}
@else
    {{ $value ?? 'Не вказано' }}
@endif
