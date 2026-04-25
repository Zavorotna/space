@extends('layouts.app')
@section('title', $user->last_name . ' ' . $user->first_name)

@section('content')
<div>
    @if($user->getFirstMediaUrl('avatar'))
        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    @endif

    <h1>{{ $user->last_name }} {{ $user->first_name }}
        @if($user->isVip()) ⭐ VIP @endif
    </h1>
    <p style="color:#888;">
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

{{-- Send notification (admin / teacher only, not to yourself) --}}
@if(auth()->check() && auth()->id() !== $user->id && (auth()->user()->isAdmin() || auth()->user()->isTeacher()))
<div style="border:1px solid #e0e0e0;border-radius:8px;padding:16px;margin-top:20px;">
    <h2 style="margin:0 0 10px;font-size:1rem;">Надіслати повідомлення</h2>
    @if(session('notify_success'))
    <p style="color:#27ae60;margin-bottom:8px;">{{ session('notify_success') }}</p>
    @endif
    <form method="POST" action="{{ route('notifications.sendToUser', $user) }}">
        @csrf
        <textarea name="message" rows="3" required placeholder="Текст повідомлення..."
                  style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;font-size:.9rem;resize:vertical;"></textarea>
        <button type="submit" style="margin-top:8px;padding:7px 16px;background:#f5a623;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:.88rem;">
            Надіслати
        </button>
    </form>
</div>
@endif
@endsection