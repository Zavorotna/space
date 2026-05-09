@extends('layouts.app')
@section('title', $user->last_name . ' ' . $user->first_name)

@section('content')
<div>
    @if($user->getFirstMediaUrl('avatar'))
        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" class="avatar avatar-lg">
    @endif

    <h1>{{ $user->last_name }} {{ $user->first_name }}
        @if($user->isVip()) ⭐ VIP @endif
    </h1>
    <p class="text-muted">
        @switch($user->role)
            @case('admin') Адміністратор @break
            @case('superadmin') Суперадмін @break
            @case('registered') Зареєстрований @break
            @default {{ $user->role }}
        @endswitch
    </p>
    @if($user->login_streak > 0)
        <p>Серія входів: {{ $user->login_streak }} днів</p>
    @endif
</div>
@include('partials._admin_user_info')

@if($user->bio)
    <h2>Про себе</h2>
    <p>{!! nl2br(e($user->bio)) !!}</p>
@endif

@if($user->achievements->count())
    <h2>Досягнення</h2>
    <ul>
    @foreach($user->achievements as $achievement)
        <li>{{ $achievement->title }}</li>
    @endforeach
    </ul>
@endif

@if(auth()->check() && auth()->id() !== $user->id && (auth()->user()->isAdmin() || auth()->user()->isTeacher()))
<div class="notify-form">
    <h2>Надіслати повідомлення</h2>
    @if(session('notify_success'))
    <p class="text-success mb-1">{{ session('notify_success') }}</p>
    @endif
    <form method="POST" action="{{ route('notifications.sendToUser', $user) }}">
        @csrf
        <textarea name="message" rows="3" required placeholder="Текст повідомлення..."></textarea>
        <button type="submit" class="btn-submit">Надіслати</button>
    </form>
</div>
@endif
@endsection