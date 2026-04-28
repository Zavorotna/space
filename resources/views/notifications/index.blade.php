@extends('layouts.app')
@section('title', 'Сповіщення')

@section('content')
<h1>Сповіщення</h1>

<form method="POST" action="{{ route('notifications.readAll') }}" class="form-inline">
    @csrf
    <button type="submit">Прочитати все</button>
</form>

<hr>

@if($notifications->isEmpty())
    <p>Немає сповіщень.</p>
@else
    @foreach($notifications as $notification)
    <div class="card {{ !$notification->is_read ? 'card--unread' : '' }}">
        <p><strong>{{ $notification->title }}</strong></p>
        <p>{{ $notification->body }}</p>
        <p>{{ $notification->created_at->format('d.m.Y H:i') }}</p>
        @if($notification->link)
            <a href="{{ $notification->link }}">Перейти</a>
        @endif
        @if(!$notification->is_read)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" class="form-inline">
                @csrf
                <button type="submit">Прочитано</button>
            </form>
        @endif
    </div>
    @endforeach

    {{ $notifications->links() }}
@endif
@endsection