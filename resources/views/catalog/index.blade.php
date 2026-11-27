@extends('layouts.app')
@section('title', $definition['title'])

@section('content')
    <div class="section-heading">
        <h1>{{ $definition['title'] }}</h1>
        @if ($canCreate)
            <a class="btn btn-primary" href="{{ route("web.$key.create") }}">Додати запис</a>
        @endif
    </div>
    @if ($key === 'users')
        <form class="search-row" method="GET" action="{{ route('web.users.index') }}">
            <label class="visually-hidden" for="search-email">Електронна пошта</label>
            <input id="search-email" class="form-control" type="email" name="email" value="{{ request('email') }}" placeholder="Електронна пошта">
            <button class="btn btn-outline-secondary">Знайти</button>
        </form>
    @endif
    @php($columns = collect($definition['fields'])->reject(fn ($field) => $field['type'] === 'password'))
    <div class="table-responsive sheet">
        <table class="table align-middle mb-0">
            <thead><tr>
                <th scope="col">ID</th>
                @foreach ($columns as $field)
                    <th scope="col">{{ $field['label'] }}</th>
                @endforeach
                @if (in_array($key, ['bills', 'payments']))
                    <th scope="col">Стан</th>
                @endif
                <th scope="col">Дії</th>
            </tr></thead>
            <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    @foreach ($columns as $name => $field)
                        <td>@include('catalog.value')</td>
                    @endforeach
                    @if (in_array($key, ['bills', 'payments']))
                        <td>@include('catalog.status')</td>
                    @endif
                    <td>@include('catalog.actions')</td>
                </tr>
            @empty
                <tr><td colspan="{{ $columns->count() + 2 + (int) in_array($key, ['bills', 'payments']) }}" class="text-center py-4">Записів поки немає.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links('pagination::bootstrap-5') }}</div>
@endsection
