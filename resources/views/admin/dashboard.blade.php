@extends('layouts.app')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">Центр управления</p>
            <h1>Админ-панель</h1>
            <p>Основные показатели и быстрый доступ к настройкам.</p>
        </div>
        <a class="button button-primary" href="{{ route('delivery.create') }}">Открыть расчёт <span>→</span></a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon icon-blue">↗</span>
            <span class="muted-label">Расчёты сегодня</span>
            <strong>{{ $todayCalculations }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-icon icon-green">сом</span>
            <span class="muted-label">Сумма сегодня</span>
            <strong>{{ number_format($todayRevenue, 0, ',', ' ') }} <small>сом</small></strong>
        </div>
        <div class="stat-card">
            <span class="stat-icon icon-purple">⌁</span>
            <span class="muted-label">Активные курьеры</span>
            <strong>{{ $couriersCount }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-icon icon-orange">⌖</span>
            <span class="muted-label">Активные точки</span>
            <strong>{{ $branchesCount }}</strong>
        </div>
    </div>

    <div class="admin-grid">
        <section class="card quick-card">
            <div class="card-heading">
                <div>
                    <h2>Настройки</h2>
                    <p>Обновляйте справочники без изменения кода.</p>
                </div>
            </div>
            <div class="quick-links">
                <a href="{{ route('admin.branches.index') }}"><span class="quick-icon icon-orange">⌖</span><span><strong>Заведения</strong><small>Адреса и координаты точек</small></span><b>→</b></a>
                <a href="{{ route('admin.tariffs.index') }}"><span class="quick-icon icon-green">₽</span><span><strong>Тарифы</strong><small>{{ $activeTariff?->name ?: 'Активный тариф не задан' }}</small></span><b>→</b></a>
                <a href="{{ route('admin.users.index') }}"><span class="quick-icon icon-purple">♙</span><span><strong>Пользователи</strong><small>Курьеры и администраторы</small></span><b>→</b></a>
                <a href="{{ route('history.index') }}"><span class="quick-icon icon-blue">↗</span><span><strong>История расчётов</strong><small>Фильтры и полный журнал</small></span><b>→</b></a>
            </div>
        </section>

        <section class="card table-card recent-admin-card">
            <div class="section-heading inside-card-heading">
                <div>
                    <p class="eyebrow">Журнал</p>
                    <h2>Последние расчёты</h2>
                </div>
                <a class="text-link" href="{{ route('history.index') }}">Все →</a>
            </div>
            @if($recentCalculations->isEmpty())
                <div class="empty-list">Расчётов пока нет.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Курьер</th><th>Адрес</th><th>Стоимость</th><th>Дата</th></tr></thead>
                        <tbody>
                        @foreach($recentCalculations as $item)
                            <tr>
                                <td>{{ $item->courier_name }}</td>
                                <td><strong>{{ $item->customer_address }}</strong><small>{{ $item->branch_name }}</small></td>
                                <td class="price-cell">{{ number_format($item->price, 0, ',', ' ') }} сом</td>
                                <td>{{ $item->created_at->format('d.m H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
