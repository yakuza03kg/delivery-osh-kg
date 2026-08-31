@extends('layouts.guest')

@section('content')
    <div class="auth-heading">
        <p class="eyebrow">Рабочее пространство</p>
        <h1>Войти в систему</h1>
        <p>Введите данные сотрудника, чтобы рассчитать доставку.</p>
    </div>

    @if($errors->any())
        <div class="flash flash-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form class="form-stack" method="POST" action="{{ route('login.store') }}">
        @csrf
        <label class="field">
            <span>Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="you@example.com">
        </label>
        <label class="field">
            <span>Пароль</span>
            <input type="password" name="password" required autocomplete="current-password" placeholder="Введите пароль">
        </label>
        <label class="checkbox-row">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
            <span>Запомнить меня</span>
        </label>
        <button class="button button-primary button-wide" type="submit">Войти</button>
    </form>

    <div class="auth-note">Учётные записи создаёт администратор.</div>
@endsection
