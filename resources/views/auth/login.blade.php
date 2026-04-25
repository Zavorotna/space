@extends('layouts.app')
@section('title', 'Вхід')
@section('content')
<h1>Вхід</h1>

{{-- Session / CSRF errors --}}
@if($errors->has('session'))
<div style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#856404;">
    {{ $errors->first('session') }}
</div>
@endif

@if($errors->any() && !$errors->has('session'))
<div style="background:#fdecea;border:1px solid #e74c3c;border-radius:6px;padding:10px 14px;margin-bottom:14px;color:#c0392b;">
    @foreach($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('login') }}" id="login-form">
    @csrf
    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="{{ old('phone') }}" required autocomplete="username">
    </div>
    <div>
        <label>Пароль</label>
        <input type="password" name="password" required autocomplete="current-password">
    </div>
    <div>
        <label><input type="checkbox" name="remember"> Запам'ятати мене</label>
    </div>
    <button type="submit">Увійти</button>
</form>

<hr style="margin:20px 0;">

<a href="{{ route('auth.google') }}" style="display:inline-block;padding:8px 16px;border:1px solid #ddd;border-radius:5px;text-decoration:none;color:#333;">
    Увійти через Google
</a>

<p>Немає акаунту? <a href="{{ route('register') }}">Зареєструватися</a></p>

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

    // Refresh every 4 minutes to stay ahead of 5-min cache TTL
    setInterval(refreshCsrf, 4 * 60 * 1000);

    // Refresh immediately when page becomes visible (device wake / tab switch)
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refreshCsrf();
    });

    // Refresh on window focus (browser switch)
    window.addEventListener('focus', refreshCsrf);
})();
</script>
@endsection