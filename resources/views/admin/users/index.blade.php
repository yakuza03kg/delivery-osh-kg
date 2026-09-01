@extends('layouts.app')

@section('content')
    <div class="page-heading heading-with-action">
        <div>
            <p class="eyebrow">Доступ к системе</p>
            <h1>Пользователи</h1>
            <p>Администраторы управляют системой, курьеры выполняют расчёты.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.users.create') }}">Добавить пользователя <span>＋</span></a>
    </div>

    <div class="card table-card">
        @if($users->isEmpty())
            <div class="empty-list">Пользователей пока нет.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Добавлен</th><th></th></tr></thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="tag {{ $user->isSuperAdmin() ? 'tag-orange' : ($user->isAdmin() ? 'tag-purple' : 'tag-blue') }}">{{ $user->roleLabel() }}</span></td>
                            <td>{{ $user->created_at->format('d.m.Y') }}</td>
                            <td class="actions-cell">
                                <a class="button button-ghost button-small" href="{{ route('admin.users.edit', $user) }}">Изменить</a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Удалить этого пользователя?')">
                                    @csrf @method('DELETE')
                                    <button class="button button-danger button-small" type="submit">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
