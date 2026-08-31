@extends('layouts.app')

@section('content')
    @php
        $zoneRows = old('zones', $tariff->exists
            ? $tariff->zones->map(fn ($zone) => ['from_km' => $zone->from_km, 'to_km' => $zone->to_km, 'price' => $zone->price])->toArray()
            : [['from_km' => '', 'to_km' => '', 'price' => '']]);
    @endphp

    <div class="page-heading">
        <p class="eyebrow">Настройки стоимости</p>
        <h1>{{ $tariff->exists ? 'Изменить тариф' : 'Новый тариф' }}</h1>
        <p>Формула сохраняется в истории каждого расчёта, поэтому прошлые суммы не изменятся.</p>
    </div>

    <form class="card form-card" method="POST" action="{{ $tariff->exists ? route('admin.tariffs.update', $tariff) : route('admin.tariffs.store') }}">
        @csrf
        @if($tariff->exists) @method('PUT') @endif

        <div class="form-grid">
            <label class="field field-span-2">
                <span>Название тарифа</span>
                <input type="text" name="name" value="{{ old('name', $tariff->name) }}" required placeholder="Стандартный тариф">
            </label>
            <label class="field">
                <span>Тип расчёта</span>
                <select name="mode" id="tariff-mode" required>
                    <option value="per_km" {{ old('mode', $tariff->mode) === 'per_km' ? 'selected' : '' }}>За километр</option>
                    <option value="progressive" {{ old('mode', $tariff->mode) === 'progressive' ? 'selected' : '' }}>Прогрессивный</option>
                    <option value="zones" {{ old('mode', $tariff->mode) === 'zones' ? 'selected' : '' }}>Тарифные зоны</option>
                </select>
            </label>
            <label class="field">
                <span>Округление расстояния</span>
                <select name="rounding" required>
                    @foreach(['none' => 'Без округления', 'ceil' => 'Вверх до целого км', 'nearest' => 'До ближайшего км', 'floor' => 'Вниз до целого км'] as $value => $label)
                        <option value="{{ $value }}" {{ old('rounding', $tariff->rounding) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="mode-section" data-mode-section="per_km">
            <div class="section-heading inline-heading"><h2>Стоимость</h2><span class="tag tag-muted">пример: 100 сом / км</span></div>
            <div class="form-grid">
                <label class="field"><span>Цена за 1 км, сом</span><input type="number" step="0.01" min="0" name="price_per_km" value="{{ old('price_per_km', $tariff->price_per_km) }}" placeholder="100"></label>
            </div>
        </div>

        <div class="mode-section" data-mode-section="progressive">
            <div class="section-heading inline-heading"><h2>Ступени тарифа</h2><span class="tag tag-muted">пример: 200 сом за первые 2 км</span></div>
            <div class="form-grid form-grid-3">
                <label class="field"><span>Первые, км</span><input type="number" step="0.01" min="0" name="base_km" value="{{ old('base_km', $tariff->base_km) }}" placeholder="2"></label>
                <label class="field"><span>Цена первых, сом</span><input type="number" step="0.01" min="0" name="base_price" value="{{ old('base_price', $tariff->base_price) }}" placeholder="200"></label>
                <label class="field"><span>Следующий км, сом</span><input type="number" step="0.01" min="0" name="additional_price_per_km" value="{{ old('additional_price_per_km', $tariff->additional_price_per_km) }}" placeholder="100"></label>
            </div>
        </div>

        <div class="mode-section" data-mode-section="zones">
            <div class="section-heading inline-heading">
                <div><h2>Тарифные зоны</h2><p>Границы включительные: 0–1 км, 1–3 км и т.д.</p></div>
                <button class="button button-ghost button-small" type="button" id="add-zone">＋ Добавить зону</button>
            </div>
            <div class="zones-list" id="zones-list">
                @foreach($zoneRows as $index => $zone)
                    <div class="zone-row">
                        <label class="field"><span>От, км</span><input type="number" step="0.01" min="0" name="zones[{{ $index }}][from_km]" value="{{ $zone['from_km'] ?? '' }}" placeholder="0"></label>
                        <label class="field"><span>До, км</span><input type="number" step="0.01" min="0" name="zones[{{ $index }}][to_km]" value="{{ $zone['to_km'] ?? '' }}" placeholder="10"></label>
                        <label class="field"><span>Цена, сом</span><input type="number" step="0.01" min="0" name="zones[{{ $index }}][price]" value="{{ $zone['price'] ?? '' }}" placeholder="100"></label>
                        <button class="remove-zone" type="button" aria-label="Удалить зону">×</button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mode-section">
            <div class="section-heading inline-heading"><h2>Ограничение стоимости</h2><span class="tag tag-muted">необязательно</span></div>
            <div class="form-grid">
                <label class="field"><span>Максимальная цена, сом</span><input type="number" step="0.01" min="0" name="max_price" value="{{ old('max_price', $tariff->max_price) }}" placeholder="Без лимита"></label>
            </div>
        </div>

        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tariff->exists ? $tariff->is_active : true) ? 'checked' : '' }}>
            <span>Сделать активным тарифом</span>
        </label>

        <div class="form-actions">
            <a class="button button-ghost" href="{{ route('admin.tariffs.index') }}">Отмена</a>
            <button class="button button-primary" type="submit">{{ $tariff->exists ? 'Сохранить' : 'Добавить' }}</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (() => {
            const mode = document.querySelector('#tariff-mode');
            const sections = document.querySelectorAll('[data-mode-section]');
            const list = document.querySelector('#zones-list');
            const addButton = document.querySelector('#add-zone');
            let nextIndex = list ? list.querySelectorAll('.zone-row').length : 0;

            const refresh = () => {
                sections.forEach((section) => {
                    section.hidden = section.dataset.modeSection !== mode.value;
                });
            };

            const bindRemoveButtons = () => {
                document.querySelectorAll('.remove-zone').forEach((button) => {
                    button.onclick = () => {
                        const rows = list.querySelectorAll('.zone-row');
                        if (rows.length > 1) button.closest('.zone-row').remove();
                    };
                });
            };

            mode?.addEventListener('change', refresh);
            addButton?.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'zone-row';
                row.innerHTML = `
                    <label class="field"><span>От, км</span><input type="number" step="0.01" min="0" name="zones[${nextIndex}][from_km]" placeholder="0"></label>
                    <label class="field"><span>До, км</span><input type="number" step="0.01" min="0" name="zones[${nextIndex}][to_km]" placeholder="10"></label>
                    <label class="field"><span>Цена, сом</span><input type="number" step="0.01" min="0" name="zones[${nextIndex}][price]" placeholder="100"></label>
                    <button class="remove-zone" type="button" aria-label="Удалить зону">×</button>`;
                list.appendChild(row);
                nextIndex++;
                bindRemoveButtons();
            });

            refresh();
            bindRemoveButtons();
        })();
    </script>
@endpush
