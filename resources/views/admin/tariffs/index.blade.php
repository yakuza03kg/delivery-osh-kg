@extends('layouts.app')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">Настройки стоимости</p>
            <h1>Тарифы</h1>
            <p>Активным считается только один тариф — он применяется к новым расчётам.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.tariffs.create') }}">Добавить тариф <span>＋</span></a>
    </div>

    <div class="cards-list">
        @forelse($tariffs as $tariff)
            <article class="card tariff-card {{ $tariff->is_active ? 'is-current' : '' }}">
                <div class="tariff-card-main">
                    <div class="tariff-title-row">
                        <h2>{{ $tariff->name }}</h2>
                        @if($tariff->is_active)<span class="tag tag-green">Активный</span>@endif
                    </div>
                    <p class="muted-text">{{ $tariff->modeLabel() }} · Округление: {{ ['none' => 'без округления', 'ceil' => 'вверх до км', 'nearest' => 'до ближайшего км', 'floor' => 'вниз до км'][$tariff->rounding] ?? $tariff->rounding }}</p>
                    <div class="tariff-summary">
                        @if($tariff->mode === \App\Models\Tariff::MODE_PER_KM)
                            <strong>{{ number_format($tariff->price_per_km, 0, ',', ' ') }} сом <small>за км</small></strong>
                        @elseif($tariff->mode === \App\Models\Tariff::MODE_PROGRESSIVE)
                            <strong>{{ number_format($tariff->base_price, 0, ',', ' ') }} сом <small>первые {{ number_format($tariff->base_km, 1, ',', ' ') }} км</small></strong>
                            <span>+ {{ number_format($tariff->additional_price_per_km, 0, ',', ' ') }} сом / км</span>
                        @else
                            <strong>{{ $tariff->zones_count }} зон</strong>
                        @endif
                        @if($tariff->max_price)<span class="tag tag-muted">лимит {{ number_format($tariff->max_price, 0, ',', ' ') }} сом</span>@endif
                    </div>
                </div>
                <div class="tariff-actions">
                    <a class="button button-ghost button-small" href="{{ route('admin.tariffs.edit', $tariff) }}">Изменить</a>
                    <form method="POST" action="{{ route('admin.tariffs.destroy', $tariff) }}" onsubmit="return confirm('Удалить этот тариф?')">
                        @csrf @method('DELETE')
                        <button class="button button-danger button-small" type="submit">Удалить</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="card empty-list">Тарифов пока нет.</div>
        @endforelse
    </div>
    <div class="pagination-wrap">{{ $tariffs->links() }}</div>
@endsection
