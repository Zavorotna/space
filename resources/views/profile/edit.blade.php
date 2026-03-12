@extends('layouts.app')
@section('title', 'Редагування профілю')

@section('content')
<h1>Редагування профілю</h1>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div>
        <label>Ім'я</label>
        <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
    </div>

    <div>
        <label>Прізвище</label>
        <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
    </div>

    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required>
    </div>

    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}">
    </div>

    <div>
        <label>Дата народження</label>
        <input type="date" name="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}" required>
    </div>

    <div>
        <label>Про себе</label>
        <textarea name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea>
    </div>

    <div>
        <label>Аватар</label>
        @if($user->getFirstMediaUrl('avatar'))
            <div>
                <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:80px; height:80px; border-radius:50%;">
            </div>
        @endif
        <input type="file" name="avatar" accept="image/*">
    </div>

    <button type="submit">Зберегти</button>
</form>

{{-- VIP extra avatars --}}
@if($user->isVip())
<hr>
<h2>Додаткові аватарки (VIP, до 5)</h2>
@php $extraAvatars = $user->getMedia('extra_avatars'); @endphp
<div>
    @foreach($extraAvatars as $avatar)
        <img src="{{ $avatar->getUrl() }}" alt="Аватар" style="width:60px; height:60px; border-radius:50%; display:inline-block; margin:3px;">
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
@endsection
