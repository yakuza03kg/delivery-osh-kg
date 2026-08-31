@extends('layouts.app')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">Справочник</p>
            <h1>Заведения</h1>
            <p>Точки, из которых курьеры начинают маршрут.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.branches.create') }}">Добавить заведение <span>＋</span></a>
    </div>

    <div class="card table-card">
        @if($branches->isEmpty())
            <div class="empty-list">Заведений пока нет.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Название</th><th>Адрес</th><th>Координаты</th><th>Статус</th><th></th></tr></thead>
                    <tbody>
                    @foreach($branches as $branch)
                        <tr>
                            <td><strong>{{ $branch->name }}</strong></td>
                            <td>{{ $branch->address }}</td>
                            <td>
                                @if($branch->hasCoordinates())
                                    <small>{{ number_format($branch->latitude, 5, '.', '') }}, {{ number_format($branch->longitude, 5, '.', '') }}</small>
                                @else
                                    <span class="tag tag-orange">Не заданы</span>
                                @endif
                            </td>
                            <td><span class="tag {{ $branch->is_active ? 'tag-green' : 'tag-muted' }}">{{ $branch->is_active ? 'Активно' : 'Выключено' }}</span></td>
                            <td class="actions-cell">
                                <a class="button button-ghost button-small" href="{{ route('admin.branches.edit', $branch) }}">Изменить</a>
                                <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" onsubmit="return confirm('Удалить это заведение?')">
                                    @csrf @method('DELETE')
                                    <button class="button button-danger button-small" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $branches->links() }}</div>
        @endif
    </div>
@endsection
