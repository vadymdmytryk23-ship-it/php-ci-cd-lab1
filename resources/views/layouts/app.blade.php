<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Комунальні послуги')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('cabinet.css') }}">
</head>
<body>
<header class="site-header">
    <div class="page header-row">
        <a class="brand" href="{{ route('home') }}">
            <span class="brand-mark" aria-hidden="true">КП</span>
            <span>Комунальні послуги</span>
        </a>
        <div class="header-actions">
            @auth
                <a href="{{ route('web.profile') }}">{{ auth()->user()->name }}</a>
                <form method="POST" action="{{ route('web.logout') }}">
                    @csrf
                    <button class="btn btn-outline-secondary btn-sm">Вийти</button>
                </form>
            @endauth
            @guest
                <a href="{{ route('web.utility-accounts.index') }}">Особові рахунки</a>
                <a href="{{ route('web.register') }}">Реєстрація</a>
                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Увійти</a>
            @endguest
        </div>
    </div>
    @auth
        <nav class="page navigation" aria-label="Основна навігація">
            @foreach (config('catalog') as $slug => $entry)
                @continue($slug === 'users' && auth()->user()->role !== 'administrator')
                <a @class(['active' => request()->routeIs("web.$slug.*")]) href="{{ route("web.$slug.index") }}">{{ $entry['title'] }}</a>
            @endforeach
            @if (auth()->user()->role === 'administrator')
                <a @class(['active' => request()->routeIs('web.statistics')]) href="{{ route('web.statistics') }}">Статистика</a>
            @endif
        </nav>
    @endauth
</header>

<main class="page content">
    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @yield('content')
</main>
</body>
</html>
