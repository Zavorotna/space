@extends('layouts.app')
@section('title', 'Викладач: ' . $user->last_name . ' ' . $user->first_name)

@section('content')
<div>
    @if($user->getFirstMediaUrl('avatar'))
        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" class="avatar avatar-lg">
    @endif

    <h1>{{ $user->last_name }} {{ $user->first_name }}
        @if($user->isVip()) ⭐ @endif
    </h1>
    <p>Викладач</p>
</div>

@if($user->bio)
    <h2>Про мене</h2>
    <p>{!! nl2br(e($user->bio)) !!}</p>
@endif

<h2>Курси</h2>
@if($user->taughtCourses->count())
    @foreach($user->taughtCourses as $course)
        <div class="teacher-course-card">
            <h3>{{ $course->title }}</h3>
            <p>{{ Str::limit($course->description, 100) }}</p>
            <a href="{{ route('courses.detail', $course) }}">Детальніше</a>
        </div>
    @endforeach
@else
    <p>Наразі немає активних курсів.</p>
@endif

@if($user->achievements->count())
    <h2>Досягнення</h2>
    <ul>
    @foreach($user->achievements as $achievement)
        <li>{{ $achievement->title }} — {{ $achievement->description }}</li>
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