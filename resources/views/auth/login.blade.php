@extends('layouts.app')
@section('title', 'Вхід до кабінету')
@section('content')
    <h1>Вхід до кабінету</h1>
    <form class="sheet editor" method="POST" action="{{ route('web.login') }}">
        @csrf
        <x-form.input name="email" label="Електронна пошта" type="email" autocomplete="username" required />
        <x-form.input name="password" label="Пароль" type="password" autocomplete="current-password" required />
        <div class="form-actions">
            <button class="btn btn-primary">Увійти</button>
            <a href="{{ route('web.register') }}">Створити профіль</a>
        </div>
    </form>
@endsection
