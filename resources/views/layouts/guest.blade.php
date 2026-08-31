<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Вход' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-page">
    <main class="guest-card">
        <a class="brand brand-centered" href="{{ url('/') }}">
            <span class="brand-mark">К</span>
            <span>
                <strong>Курьер KG</strong>
                <small>расчёт доставки</small>
            </span>
        </a>
        @yield('content')
    </main>
</body>
</html>
