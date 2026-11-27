@extends('layouts.app')
@section('title', 'Мій профіль')
@section('content')
    <h1>Мій профіль</h1>
    <div class="sheet editor">
        <dl class="detail-list">
            <dt>Ім’я</dt><dd>{{ $user->name }}</dd>
            <dt>Електронна пошта</dt><dd>{{ $user->email }}</dd>
            <dt>Роль</dt><dd>{{ $user->role === 'administrator' ? 'Адміністратор' : 'Споживач' }}</dd>
        </dl>
    </div>
    @if (app()->isLocal() && config('app.sentry_demo_enabled'))
        <form class="mt-3" method="POST" action="{{ route('web.sentry-demo') }}">
            @csrf
            <button class="btn btn-outline-secondary">Перевірити Sentry</button>
        </form>
    @endif
@endsection
