@extends('layouts.app')
@section('title', 'Студент: ' . $user->last_name . ' ' . $user->first_name)

@section('content')
<div>
    @if($user->getFirstMediaUrl('avatar'))
        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    @endif

    <h1>{{ $user->last_name }} {{ $user->first_name }}
        @if($user->isVip()) ⭐ VIP @endif
    </h1>
    <p>Студент</p>
    @if($user->login_streak > 0)
        <p>Серія входів: {{ $user->login_streak }} днів</p>
    @endif
</div>

{{-- Parents --}}
@if($user->parents->count())
    <h2>Батьки</h2>
    @foreach($user->parents as $parent)
        <div>
            <a href="{{ route('profile.show', $parent) }}">{{ $parent->last_name }} {{ $parent->first_name }}</a>
            @if($parent->phone) — {{ $parent->phone }} @endif
        </div>
    @endforeach
@endif

{{-- Courses --}}
@if($user->enrollments->count())
    <h2>Курси</h2>
    @foreach($user->enrollments as $course)
        <div>
            <strong>{{ $course->title }}</strong>
            —
            @switch($course->pivot->status)
                @case('active') Активний @break
                @case('completed') Завершений @break
                @case('pending') Очікує @break
                @default {{ $course->pivot->status }}
            @endswitch
            @if($course->start_date || $course->end_date)
                ({{ $course->start_date?->format('d.m.Y') ?? '?' }} — {{ $course->end_date?->format('d.m.Y') ?? '?' }})
            @endif
        </div>
    @endforeach
@endif

{{-- Certificates --}}
@if($user->certificates->count())
    <h2>Сертифікати</h2>
    @foreach($user->certificates as $cert)
        <div>
            <p>{{ $cert->course->title ?? '—' }} — {{ $cert->success_rate }}%
                (@switch($cert->type)
                    @case('bw') ЧБ @break
                    @case('color') Кольоровий @break
                    @case('guaranteed') З гарантією @break
                @endswitch)
            </p>
        </div>
    @endforeach
@endif

{{-- Achievements --}}
@if($user->achievements->count())
    <h2>Досягнення</h2>
    <ul>
    @foreach($user->achievements as $achievement)
        <li>{{ $achievement->title }}</li>
    @endforeach
    </ul>
@endif

{{-- Transfer link --}}
@auth
    @if(auth()->id() !== $user->id)
        <p><a href="{{ route('wallet.transfer') }}?to={{ $user->id }}">Переказати монети</a></p>
    @endif
@endauth

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
