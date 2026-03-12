@extends('layouts.app')
@section('title', 'Сповіщення')

@section('content')
<h1>Сповіщення</h1>

<form method="POST" action="{{ route('notifications.readAll') }}" style="display:inline;">
    @csrf
    <button type="submit">Прочитати все</button>
</form>

<hr>

@if($notifications->isEmpty())
    <p>Немає сповіщень.</p>
@else
    @foreach($notifications as $notification)
    <div style="border:1px solid #ccc; padding:10px; margin:5px 0; {{ $notification->is_read ? '' : 'background:#e3f2fd;' }}">
        <p><strong>{{ $notification->title }}</strong></p>
        <p>{{ $notification->body }}</p>
        <p>{{ $notification->created_at->format('d.m.Y H:i') }}</p>
        @if($notification->link)
            <a href="{{ $notification->link }}">Перейти</a>
        @endif
        @if(!$notification->is_read)
            <form method="POST" action="{{ route('notifications.read', $notification) }}" style="display:inline;">
                @csrf
                <button type="submit">Прочитано</button>
            </form>
        @endif
    </div>
    @endforeach

    {{ $notifications->links() }}
@endif
@endsection
