@extends('layouts.app')

@section('content')
    @php
        $geocoder = $counters->get(\App\Models\ApiUsageCounter::SERVICE_GEOCODER);
        $routing = $counters->get(\App\Models\ApiUsageCounter::SERVICE_ROUTING);
    @endphp

    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">Контроль лимитов</p>
            <h1>Запросы 2GIS API</h1>
            <p>Внесите текущие данные из Platform Manager. После этого счётчик будет автоматически уменьшать остаток при новых запросах приложения.</p>
        </div>
        <a class="button button-ghost" href="{{ route('admin.dashboard') }}">← В админ-панель</a>
    </div>

    <div class="quota-settings-grid">
        @foreach([$geocoder, $routing] as $counter)
            <section class="card quota-summary-card">
                <span class="muted-label">{{ $counter->label() }}</span>
                <strong>{{ number_format($counter->remaining(), 0, ',', ' ') }} <small>осталось</small></strong>
                <p>{{ number_format($counter->totalUsed(), 0, ',', ' ') }} из {{ number_format($counter->quota_limit, 0, ',', ' ') }} использовано</p>
            </section>
        @endforeach
    </div>

    <form class="card form-card narrow-card" method="POST" action="{{ route('admin.api-usage.update') }}">
        @csrf
        @method('PUT')
        <div class="card-heading">
            <div>
                <h2>Синхронизация с Platform Manager</h2>
                <p>Использованное значение берите из раздела «Сервисы в демо-ключе» или «Статистика» 2GIS.</p>
            </div>
        </div>

        <div class="form-grid">
            <label class="field">
                <span>Лимит Geocoder API</span>
                <input type="number" name="geocoder_quota_limit" min="1" required value="{{ old('geocoder_quota_limit', $geocoder->quota_limit) }}">
            </label>
            <label class="field">
                <span>Использовано Geocoder API</span>
                <input type="number" name="geocoder_baseline_used" min="0" required value="{{ old('geocoder_baseline_used', $geocoder->totalUsed()) }}">
            </label>
            <label class="field">
                <span>Лимит Routing API</span>
                <input type="number" name="routing_quota_limit" min="1" required value="{{ old('routing_quota_limit', $routing->quota_limit) }}">
            </label>
            <label class="field">
                <span>Использовано Routing API</span>
                <input type="number" name="routing_baseline_used" min="0" required value="{{ old('routing_baseline_used', $routing->totalUsed()) }}">
            </label>
            <label class="field field-span-2">
                <span>Дата окончания лимита</span>
                <input type="date" name="period_ends_at" value="{{ old('period_ends_at', $geocoder->period_ends_at?->format('Y-m-d')) }}">
            </label>
        </div>

        <label class="checkbox-row quota-reset-row">
            <input type="checkbox" name="reset_local_counters" value="1" checked>
            <span>Начать новый внутренний отсчёт после синхронизации</span>
        </label>

        <div class="notice notice-neutral">Например, если Platform Manager показывает 56 / 1 000 для Geocoder и 22 / 1 000 для Routing, внесите 56 и 22. Внутренний счётчик начнёт отслеживать новые запросы с нуля.</div>

        <div class="form-actions">
            <a class="button button-ghost" href="{{ route('admin.dashboard') }}">Отмена</a>
            <button class="button button-primary" type="submit">Сохранить и синхронизировать</button>
        </div>
    </form>
@endsection
