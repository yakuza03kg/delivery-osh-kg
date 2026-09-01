@extends('layouts.app')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">{{ $isAdmin ? 'Контроль операций' : 'Личная статистика' }}</p>
            <h1>{{ $isAdmin ? 'История всех расчётов' : 'Моя история' }}</h1>
            <p>{{ $isAdmin ? 'Фильтруйте расчёты по курьеру, заведению и дате.' : 'Все ваши расчёты собраны в одном месте.' }}</p>
        </div>
        <a class="button button-primary" href="{{ route('delivery.create') }}">Новый расчёт <span>→</span></a>
    </div>

    @if($isAdmin)
        <form class="card filters-card" method="GET" action="{{ route('history.index') }}">
            <label class="field compact-field">
                <span>Курьер</span>
                <select name="courier_id">
                    <option value="">Все курьеры</option>
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}" {{ request('courier_id') == $courier->id ? 'selected' : '' }}>{{ $courier->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field compact-field">
                <span>Заведение</span>
                <select name="branch_id">
                    <option value="">Все заведения</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="field compact-field">
                <span>От</span>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </label>
            <label class="field compact-field">
                <span>До</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </label>
            <button class="button button-dark" type="submit">Применить</button>
            <a class="button button-ghost" href="{{ route('history.index') }}">Сбросить</a>
        </form>
    @endif

    <div class="card table-card">
        @if($calculations->isEmpty())
            <div class="empty-list">По выбранным условиям расчётов нет.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        @if($isAdmin)<th>Курьер</th>@endif
                        <th>Заведение</th>
                        <th>Адрес клиента</th>
                        <th>Маршрут</th>
                        <th>Стоимость</th>
                        <th>Дата</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($calculations as $calculation)
                        <tr>
                            @if($isAdmin)<td><strong>{{ $calculation->courier_name }}</strong></td>@endif
                            <td>{{ $calculation->branch_name }}</td>
                            <td><strong>{{ $calculation->customer_address }}</strong><small>{{ $calculation->resolved_address }}</small></td>
                            <td>{{ number_format($calculation->distance_km, 1, ',', ' ') }} км @if($calculation->durationLabel())<small>{{ $calculation->durationLabel() }}</small>@endif</td>
                            <td class="price-cell">{{ number_format($calculation->price, 0, ',', ' ') }} сом</td>
                            <td>{{ $calculation->formattedCreatedAt() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $calculations->links() }}</div>
        @endif
    </div>
@endsection
