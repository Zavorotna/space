@extends('layouts.app')
@section('title', 'Реєстрація')
@section('content')
<h1>Реєстрація</h1>
<form method="POST" action="{{ route('register') }}">
    @csrf
    <div><label>Ім'я</label><input type="text" name="first_name" value="{{ old('first_name') }}" required></div>
    <div><label>Прізвище</label><input type="text" name="last_name" value="{{ old('last_name') }}" required></div>
    <div><label>Номер телефону</label><input type="text" name="phone" value="{{ old('phone') }}" required></div>
    <div><label>Дата народження</label><input type="date" name="birthday" value="{{ old('birthday') }}" required></div>
    <div><label>Пароль</label><input type="password" name="password" required></div>
    <div><label>Підтвердження паролю</label><input type="password" name="password_confirmation" required></div>
    <button type="submit">Зареєструватися</button>
</form>
<p>зареєструватися через</p>
<a href="{{ route('auth.google') }}">Google</a>
<p>Маєш акаунт? <a href="{{ route('login') }}">Увійти</a></p>
@endsection
