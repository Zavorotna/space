@extends('layouts.app')
@section('title', 'Вхід')
@section('content')
<h1>Вхід</h1>

@if($errors->has('session'))
<div class="alert-box alert-box--warn">
    {{ $errors->first('session') }}
</div>
@endif

@if($errors->any() && !$errors->has('session'))
<div class="alert-box alert-box--error">
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

<hr>

<a href="{{ route('auth.google') }}" class="btn-google">Увійти через Google</a>

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

    setInterval(refreshCsrf, 4 * 60 * 1000);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refreshCsrf();
    });

    window.addEventListener('focus', refreshCsrf);
})();
</script>
@endsection