@extends('layouts.app')
@section('title', 'Вхід')
@section('content')
<h1>Вхід</h1>
<form method="POST" action="{{ route('login') }}">
    @csrf
    <div><label>Номер телефону</label><input type="text" name="phone" value="{{ old('phone') }}" required></div>
    <div><label>Пароль</label><input type="password" name="password" required></div>
    <div><label><input type="checkbox" name="remember"> Запам'ятати мене</label></div>
    <button type="submit">Увійти</button>
</form>
<a href="{{ route('auth.google') }}">Увійти через Google</a>
<p>Немає акаунту? <a href="{{ route('register') }}">Зареєструватися</a></p>

<script>
setInterval(function () {
    fetch('/csrf-token')
        .then(r => r.json())
        .then(data => {
            document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.token);
            window.csrfToken = data.token;
        });
}, 10 * 60 * 1000);
</script>
@endsection
