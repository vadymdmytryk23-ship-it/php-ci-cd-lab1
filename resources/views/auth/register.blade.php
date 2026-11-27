@extends('layouts.app')
@section('title', 'Реєстрація')
@section('content')
    <h1>Реєстрація</h1>
    <form class="sheet editor" method="POST" action="{{ route('web.register.store') }}">
        @csrf
        <x-form.input name="name" label="Ім’я" required />
        <x-form.input name="email" label="Електронна пошта" type="email" required />
        <x-form.input name="password" label="Пароль" type="password" autocomplete="new-password" required />
        <x-form.input name="password_confirmation" label="Повторіть пароль" type="password" autocomplete="new-password" required />
        <button class="btn btn-primary">Зареєструватися</button>
    </form>
@endsection
