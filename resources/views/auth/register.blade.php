@extends('layouts.app')
@section('title', 'Реєстрація')
@section('content')
<h1>Реєстрація</h1>

{{-- Google consent error --}}
@if(session('google_error'))
<div style="background:#fdecea;border:1px solid #e74c3c;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#c0392b;">
    {{ session('google_error') }}
</div>
@endif

{{-- Session / CSRF errors --}}
@if($errors->has('session'))
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#856404;">
    {{ $errors->first('session') }}
</div>
@endif

{{-- Google registration --}}
<a href="{{ route('auth.google') }}" style="display:inline-block;padding:8px 16px;border:1px solid #ddd;border-radius:5px;text-decoration:none;color:#333;margin-bottom:16px;">
    Зареєструватися через Google
</a>
<p style="color:#888;font-size:.85em;margin:0 0 16px;">
    Натискаючи «Зареєструватися через Google», ви надаєте згоду на використання ваших персональних даних Google.
</p>

<hr style="margin:0 0 20px;">

{{-- Manual registration form --}}
@if($errors->any() && !$errors->has('session'))
<div style="background:#fdecea;border:1px solid #e74c3c;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#c0392b;">
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