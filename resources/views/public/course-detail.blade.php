@extends('layouts.app')
@section('title', $course->title)
@section('content')
<h1>{{ $course->title }}</h1>
@if($course->getFirstMediaUrl('cover'))
    <img src="{{ $course->getFirstMediaUrl('cover') }}" alt="{{ $course->title }}" width="300">
@endif
<p>{{ $course->description }}</p>
@if($course->program)
    <h2>Програма</h2>
    <div>{!! nl2br(e($course->program)) !!}</div>
@endif
<p>Викладач: {{ $course->teacher->full_name }}</p>
<p>Ціна: {{ $course->price }} грн/{{ $course->billing_period === 'monthly' ? 'міс' : 'разово' }}</p>
<p>Дата початку: {{ $course->start_date?->format('d.m.Y') ?? 'Не визначено' }}</p>
<p>Статус: {{ $course->status }}</p>

@auth
    @if(!$course->students()->where('user_id', auth()->id())->exists())
        <form method="POST" action="{{ route('courses.apply', $course) }}">
            @csrf
            <textarea name="note" placeholder="Коментар до заявки"></textarea>
            <button type="submit">Подати заявку</button>
        </form>
    @else
        <a href="{{ route('courses.student.show', $course) }}">Перейти до курсу</a>
    @endif
@endauth

<h2>Відгуки</h2>
@foreach($course->reviews as $review)
<div>
    <strong>{{ $review->user->full_name }}</strong> — {{ $review->rating }}/5
    <p>{{ $review->text }}</p>
</div>
@endforeach
@endsection
