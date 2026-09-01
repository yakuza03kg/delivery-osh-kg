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

    <section class="card api-quota-card">
        <div class="section-heading inside-card-heading">
            <div>
                <p class="eyebrow">Лимиты 2GIS</p>
                <h2>Остаток запросов API</h2>
            </div>
            <a class="button button-ghost button-small" href="{{ route('admin.api-usage.index') }}">Сверить с 2GIS</a>
        </div>
        <div class="quota-grid">
            @foreach($twoGisCounters as $counter)
                <div class="quota-item">
                    <div class="quota-topline">
                        <span>{{ $counter->label() }}</span>
                        <strong>{{ number_format($counter->remaining(), 0, ',', ' ') }}</strong>
                    </div>
                    <div class="quota-progress"><span style="width: {{ $counter->usagePercent() }}%"></span></div>
                    <p>Использовано {{ number_format($counter->totalUsed(), 0, ',', ' ') }} из {{ number_format($counter->quota_limit, 0, ',', ' ') }}</p>
                    @if($counter->period_ends_at)
                        <small>Лимит до {{ $counter->period_ends_at->format('d.m.Y') }}</small>
                    @else
                        <small>Укажите дату окончания в настройках</small>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="quota-note">После синхронизации приложение автоматически учитывает каждый успешный запрос к 2GIS. Сверяйте показания с Platform Manager при смене периода.</p>
    </section>

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
                <a href="{{ route('admin.api-usage.index') }}"><span class="quick-icon icon-blue">2Г</span><span><strong>Лимиты 2GIS</strong><small>Остаток геокодирования и маршрутов</small></span><b>→</b></a>
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
