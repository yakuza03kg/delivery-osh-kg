@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <p class="eyebrow">Справочник заведений</p>
        <h1>{{ $branch->exists ? 'Изменить заведение' : 'Новое заведение' }}</h1>
        <p>Координаты нужны для построения маршрута от этой точки.</p>
    </div>

    <form class="card form-card narrow-card" method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}">
        @csrf
        @if($branch->exists) @method('PUT') @endif
        <div class="form-grid">
            <label class="field field-span-2">
                <span>Название</span>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required placeholder="Барбол — Центр">
            </label>
            <label class="field field-span-2">
                <span>Адрес</span>
                <input type="text" name="address" value="{{ old('address', $branch->address) }}" required placeholder="Бишкек, ул. Ленина, 100">
            </label>
            <label class="field">
                <span>Широта</span>
                <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $branch->latitude) }}" placeholder="42.8746000">
            </label>
            <label class="field">
                <span>Долгота</span>
                <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $branch->longitude) }}" placeholder="74.5698000">
            </label>
        </div>

        <div class="notice notice-neutral">Координаты можно взять из карточки заведения в выбранном картографическом сервисе. Если их не указать, точка останется выключенной для расчёта до заполнения.</div>

        <label class="checkbox-row">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->exists ? $branch->is_active : true) ? 'checked' : '' }}>
            <span>Заведение доступно курьерам</span>
        </label>

        <div class="form-actions">
            <a class="button button-ghost" href="{{ route('admin.branches.index') }}">Отмена</a>
            <button class="button button-primary" type="submit">{{ $branch->exists ? 'Сохранить' : 'Добавить' }}</button>
        </div>
    </form>
@endsection
