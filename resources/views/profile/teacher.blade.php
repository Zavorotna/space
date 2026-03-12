@extends('layouts.app')
@section('title', 'Викладач: ' . $user->last_name . ' ' . $user->first_name)

@section('content')
<div>
    @if($user->getFirstMediaUrl('avatar'))
        <img src="{{ $user->getFirstMediaUrl('avatar') }}" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
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

{{-- Courses taught --}}
<h2>Курси</h2>
@if($user->taughtCourses->count())
    @foreach($user->taughtCourses as $course)
        <div style="border:1px solid #ccc; padding:10px; margin:5px 0;">
            <h3>{{ $course->title }}</h3>
            <p>{{ Str::limit($course->description, 100) }}</p>
            <a href="{{ route('courses.detail', $course) }}">Детальніше</a>
        </div>
    @endforeach
@else
    <p>Наразі немає активних курсів.</p>
@endif

{{-- Achievements --}}
@if($user->achievements->count())
    <h2>Досягнення</h2>
    <ul>
    @foreach($user->achievements as $achievement)
        <li>{{ $achievement->title }} — {{ $achievement->description }}</li>
    @endforeach
    </ul>
@endif
@endsection
