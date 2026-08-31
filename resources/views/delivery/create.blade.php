@extends('layouts.app')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">Расчёт доставки</p>
            <h1>Рассчитать доставку</h1>
            <p>Выберите точку отправления и укажите адрес клиента.</p>
        </div>
        <div class="provider-pill">
            <span class="status-dot"></span>
            {{ $provider->name() === '2gis' ? '2GIS' : ($provider->name() === 'nominatim_osrm' ? 'OSM / OSRM' : 'Демо-режим') }}
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="card calculator-card">
            <div class="card-heading">
                <div>
                    <h2>Новый расчёт</h2>
                    <p>Маршрут строится по автомобильным дорогам.</p>
                </div>
                <span class="step-mark">01</span>
            </div>

            <form class="form-stack" method="POST" action="{{ route('delivery.store') }}">
                @csrf
                <label class="field">
                    <span>Заведение</span>
                    <select name="branch_id" required {{ $branches->isEmpty() ? 'disabled' : '' }}>
                        <option value="">Выберите заведение</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} — {{ $branch->address }}
                            </option>
                        @endforeach
                    </select>
                    @if($branches->isEmpty())
                        <small class="field-hint">Администратору нужно добавить хотя бы одно активное заведение.</small>
                    @endif
                </label>

                <label class="field">
                    <span>Адрес клиента</span>
                    <textarea name="customer_address" rows="3" required placeholder="Например: Бишкек, ул. Курманжан Датка, 25">{{ old('customer_address') }}</textarea>
                    <small class="field-hint">Укажите город, улицу и номер дома — так карта найдёт адрес точнее.</small>
                </label>

                @if($provider->name() === 'demo')
                    <div class="notice notice-neutral">
                        Демо-режим включён для локального запуска. Для проверки введите координаты клиента: <code>42.8746, 74.5698</code>.
                    </div>
                @endif

                @if($activeTariff)
                    <div class="tariff-preview">
                        <div>
                            <span class="muted-label">Активный тариф</span>
                            <strong>{{ $activeTariff->name }}</strong>
                        </div>
                        <span class="tag tag-green">{{ $activeTariff->modeLabel() }}</span>
                    </div>
                @else
                    <div class="notice notice-warning">Активный тариф не настроен. Расчёт пока недоступен.</div>
                @endif

                <button class="button button-primary button-large" type="submit" {{ $branches->isEmpty() || ! $activeTariff ? 'disabled' : '' }}>
                    <span>Рассчитать доставку</span>
                    <span aria-hidden="true">→</span>
                </button>
            </form>
        </section>

        <aside class="side-column">
            @if($calculation)
                <section class="result-card">
                    <div class="result-topline">
                        <span class="eyebrow">Готово</span>
                        <span class="result-check">✓</span>
                    </div>
                    <h2>Стоимость доставки</h2>
                    <p class="result-address">{{ $calculation->resolved_address ?: $calculation->customer_address }}</p>
                    <div class="result-price">{{ number_format($calculation->price, 0, ',', ' ') }} <small>сом</small></div>
                    <div class="result-metrics">
                        <div>
                            <span>Расстояние</span>
                            <strong>{{ number_format($calculation->distance_km, 1, ',', ' ') }} км</strong>
                        </div>
                        @if($calculation->durationLabel())
                            <div>
                                <span>В пути</span>
                                <strong>{{ $calculation->durationLabel() }}</strong>
                            </div>
                        @endif
                    </div>
                    <div class="result-meta">{{ $calculation->branch_name }} · {{ $calculation->created_at->format('d.m.Y H:i') }}</div>
                </section>
            @else
                <section class="empty-result card">
                    <div class="empty-icon">⌖</div>
                    <h2>Здесь появится результат</h2>
                    <p>После расчёта здесь появятся расстояние, стоимость и время маршрута.</p>
                </section>
            @endif

            <section class="card tips-card">
                <div class="card-heading compact">
                    <h2>Быстрый порядок</h2>
                    <span class="step-mark muted">02</span>
                </div>
                <ol class="steps-list">
                    <li><span>1</span> Выберите заведение</li>
                    <li><span>2</span> Введите полный адрес</li>
                    <li><span>3</span> Покажите клиенту итог</li>
                </ol>
            </section>
        </aside>
    </div>

    @auth
        <section class="section-block">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Последние операции</p>
                    <h2>Мои расчёты</h2>
                </div>
                <a class="text-link" href="{{ route('history.index') }}">Вся история →</a>
            </div>

            @if($recentCalculations->isEmpty())
                <div class="card empty-list">Расчётов пока нет.</div>
            @else
                <div class="card table-card">
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Адрес клиента</th>
                                <th>Заведение</th>
                                <th>Расстояние</th>
                                <th>Стоимость</th>
                                <th>Время</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentCalculations as $item)
                                <tr>
                                    <td><strong>{{ $item->customer_address }}</strong><small>{{ $item->resolved_address }}</small></td>
                                    <td>{{ $item->branch_name }}</td>
                                    <td>{{ number_format($item->distance_km, 1, ',', ' ') }} км</td>
                                    <td class="price-cell">{{ number_format($item->price, 0, ',', ' ') }} сом</td>
                                    <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>
    @endauth
@endsection
