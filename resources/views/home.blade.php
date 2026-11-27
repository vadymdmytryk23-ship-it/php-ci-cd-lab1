@extends('layouts.app')
@section('title', 'Система оплати комунальних послуг')

@section('content')
    <section class="welcome-panel">
        <h1>Система оплати<br>комунальних послуг</h1>
        <p>Переглядайте особові рахунки, подавайте показники й отримуйте квитанції після оплати.</p>
        <a class="btn btn-primary" href="{{ route(auth()->check() ? 'web.utility-accounts.index' : 'login') }}">Відкрити особистий кабінет</a>
    </section>
    <div class="home-links">
        @foreach (['utility-accounts' => ['Особові рахунки', 'Номери рахунків та підключені послуги.'], 'meter-readings' => ['Показники', 'Подання та перегляд показників лічильників.'], 'bills' => ['Нарахування', 'Суми до сплати, платежі та квитанції.']] as $slug => [$name, $description])
            <a class="home-link" href="{{ route(auth()->check() ? "web.$slug.index" : 'login') }}">
                <strong>{{ $name }}</strong>
                <span>{{ $description }}</span>
            </a>
        @endforeach
    </div>
@endsection
