<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Курьер KG' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <a class="brand" href="{{ route('delivery.create') }}">
                <span class="brand-mark">К</span>
                <span>
                    <strong>Курьер KG</strong>
                    <small>расчёт доставки</small>
                </span>
            </a>

            <nav class="main-nav" aria-label="Основная навигация">
                <a class="nav-link {{ request()->routeIs('delivery.*') ? 'is-active' : '' }}" href="{{ route('delivery.create') }}">Расчёт</a>
                <a class="nav-link {{ request()->routeIs('history.*') ? 'is-active' : '' }}" href="{{ route('history.index') }}">История</a>
                @if(auth()->user()->isAdmin())
                    <a class="nav-link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">Админ-панель</a>
                @endif
            </nav>

            <div class="account-menu">
                <span class="account-name">{{ auth()->user()->name }}</span>
                <span class="role-badge">{{ auth()->user()->isAdmin() ? 'Администратор' : 'Курьер' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button-ghost button-small" type="submit">Выйти</button>
                </form>
            </div>
        </header>

        <main class="page-container">
            @if(session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="flash flash-error">
                    <strong>Проверьте данные</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
