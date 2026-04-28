@extends('layouts.app')
@section('title', 'Реєстрація')
@section('content')
<h1>Реєстрація</h1>

@if(session('google_error'))
<div class="alert-box alert-box--error">
    {{ session('google_error') }}
</div>
@endif

@if($errors->has('session'))
<div class="alert-box alert-box--warn">
    {{ $errors->first('session') }}
</div>
@endif

<a href="{{ route('auth.google') }}" class="btn-google">Зареєструватися через Google</a>
<p class="auth-hint">
    Натискаючи «Зареєструватися через Google», ви надаєте згоду на використання ваших персональних даних Google.
</p>

<hr>

@if($errors->any() && !$errors->has('session'))
<div class="alert-box alert-box--error">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('register') }}" id="register-form">
    @csrf
    <div>
        <label>Ім'я</label>
        <input type="text" name="first_name" value="{{ old('first_name') }}" required>
    </div>
    <div>
        <label>Прізвище</label>
        <input type="text" name="last_name" value="{{ old('last_name') }}" required>
    </div>
    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required autocomplete="username">
    </div>
    <div>
        <label>Дата народження</label>
        <input type="date" name="birthday" value="{{ old('birthday') }}" required>
    </div>
    <div>
        <label>Пароль</label>
        <input type="password" name="password" required autocomplete="new-password">
    </div>
    <div>
        <label>Підтвердження паролю</label>
        <input type="password" name="password_confirmation" required autocomplete="new-password">
    </div>
    <button type="submit">Зареєструватися</button>
</form>

<p>Маєш акаунт? <a href="{{ route('login') }}">Увійти</a></p>

<script>
(function () {
    function refreshCsrf() {
        fetch('/csrf-token')
            .then(r => r.json())
            .then(data => {
                document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.token);
                window.csrfToken = data.token;
            })
            .catch(() => {});
    }

    setInterval(refreshCsrf, 4 * 60 * 1000);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refreshCsrf();
    });

    window.addEventListener('focus', refreshCsrf);
})();
</script>
@endsection