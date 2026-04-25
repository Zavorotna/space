@extends('layouts.app')
@section('title', 'Батько: ' . $user->last_name . ' ' . $user->first_name)

@section('content')
<div>
    @if($user->getFirstMediaUrl('avatar'))
        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    @endif

    <h1>{{ $user->last_name }} {{ $user->first_name }}
        @if($user->isVip()) ⭐ VIP @endif
    </h1>
    <p>Батько/Мати</p>
    @if($user->login_streak > 0)
        <p>Серія входів: {{ $user->login_streak }} днів</p>
    @endif
</div>

{{-- Children --}}
<h2>Діти</h2>
@if($user->children->count())
    @foreach($user->children as $child)
        <div>
            <a href="{{ route('profile.show', $child) }}">{{ $child->last_name }} {{ $child->first_name }}</a>
            <span>({{ $child->role }})</span>

            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('admin.users.unlinkChild', [$user, $child]) }}" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Скасувати зв\'язок з {{ $child->first_name }}?')">
                        Скасувати зв'язок
                    </button>
                </form>
            @endif
        </div>
    @endforeach
@else
    <p>Дітей не додано.</p>
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

@auth
    @if(auth()->id() !== $user->id)
        <p><a href="{{ route('wallet.transfer') }}?to={{ $user->id }}">Переказати монети</a></p>
    @endif
@endauth

{{-- Send notification (admin / teacher, not to yourself) --}}
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