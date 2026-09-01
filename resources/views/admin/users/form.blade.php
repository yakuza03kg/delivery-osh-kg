@extends('layouts.app')

@section('content')
    <div class="page-heading">
        <p class="eyebrow">Доступ к системе</p>
        <h1>{{ $user->exists ? 'Изменить пользователя' : 'Новый пользователь' }}</h1>
        <p>{{ $user->exists ? 'Оставьте пароль пустым, чтобы сохранить текущий.' : 'Пароль должен быть не короче 8 символов.' }}</p>
    </div>

    <form class="card form-card narrow-card" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif
        <div class="form-grid">
            <label class="field field-span-2"><span>Имя</span><input type="text" name="name" value="{{ old('name', $user->name) }}" required placeholder="Азамат"></label>
            <label class="field field-span-2"><span>Email</span><input type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="azamat@example.com"></label>
            <label class="field"><span>Роль</span><select name="role" required><option value="courier" {{ old('role', $user->role) === 'courier' ? 'selected' : '' }}>Курьер</option><option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор</option><option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Супер-администратор</option></select></label>
            <div></div>
            <label class="field"><span>Пароль</span><input type="password" name="password" {{ $user->exists ? '' : 'required' }} autocomplete="new-password"></label>
            <label class="field"><span>Повторите пароль</span><input type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }} autocomplete="new-password"></label>
        </div>

        <div class="form-actions">
            <a class="button button-ghost" href="{{ route('admin.users.index') }}">Отмена</a>
            <button class="button button-primary" type="submit">{{ $user->exists ? 'Сохранить' : 'Добавить' }}</button>
        </div>
    </form>
@endsection
