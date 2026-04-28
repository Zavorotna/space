@extends('layouts.app')
@section('title', 'Редагування профілю')

@section('content')
<h1>Редагування профілю</h1>

@if(session('success'))
<p class="text-success mb-1">{{ session('success') }}</p>
@endif

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div>
        <label>Ім'я</label>
        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
        @error('first_name') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Прізвище</label>
        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
        @error('last_name') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required>
        @error('phone') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">
        @error('email') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Дата народження</label>
        @if($user->birthday)
            <input type="date" value="{{ $user->birthday->format('Y-m-d') }}" disabled class="input-locked">
            <span class="text-xs text-muted">Дату народження неможливо змінити після встановлення.</span>
        @else
            <input type="date" name="birthday" value="{{ old('birthday') }}" required>
            @error('birthday') <span class="field-error">{{ $message }}</span> @enderror
        @endif
    </div>

    <div>
        <label>Про себе</label>
        <textarea name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea>
        @error('bio') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Аватар</label>
        @if($user->getFirstMediaUrl('avatar'))
            <div>
                <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" class="avatar avatar-md">
            </div>
        @endif
        <input type="file" name="avatar" accept="image/*">
        @error('avatar') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <button type="submit">Зберегти</button>
</form>

@if($user->isVip())
<hr>
<h2>Додаткові аватарки (VIP, до 5)</h2>
@php $extraAvatars = $user->getMedia('extra_avatars'); @endphp
<div>
    @foreach($extraAvatars as $avatar)
        <img src="{{ $avatar->getUrl() }}" alt="Аватар" class="avatar avatar-sm avatar-inline">
    @endforeach
</div>
@if($extraAvatars->count() < 5)
    <form method="POST" action="{{ route('profile.avatar.extra') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="avatar" accept="image/*" required>
        <button type="submit">Додати аватарку ({{ $extraAvatars->count() }}/5)</button>
    </form>
@else
    <p>Максимум 5 аватарок.</p>
@endif
@endif

<hr>
<h2>Зміна пароля</h2>

@if(session('password_success'))
<p class="text-success mb-1">{{ session('password_success') }}</p>
@endif

<form method="POST" action="{{ route('profile.password') }}">
    @csrf @method('PUT')

    <div>
        <label>Поточний пароль</label>
        <input type="password" name="current_password" required>
        @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Новий пароль</label>
        <input type="password" name="password" required minlength="8">
        @error('password') <span class="field-error">{{ $message }}</span> @enderror
    </div>

    <div>
        <label>Повторіть новий пароль</label>
        <input type="password" name="password_confirmation" required>
    </div>

    <button type="submit">Змінити пароль</button>
</form>
@endsection