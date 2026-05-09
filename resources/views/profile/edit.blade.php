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
<h2>Google акаунт</h2>
@if($user->google_id)
<p class="text-success">✓ Google акаунт прив'язано</p>
<form method="POST" action="{{ route('auth.google.unlink') }}" class="form-inline">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-ghost btn-sm"
            onclick="return confirm('Від\'язати Google акаунт?')">Від'язати Google</button>
</form>
@else
<p class="text-sm text-muted">Прив'яжіть Google акаунт для швидкого входу без пароля.</p>
<a href="{{ route('auth.google.link') }}" class="btn btn-google">
    <svg width="18" height="18" viewBox="0 0 18 18" style="vertical-align:middle;margin-right:6px;">
        <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
        <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
        <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.175 0 7.55 0 9s.348 2.825.957 4.039l3.007-2.332z"/>
        <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z"/>
    </svg>
    Прив'язати Google
</a>
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