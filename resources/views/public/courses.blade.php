@extends('layouts.app')
@section('title', 'Курси')
@section('content')
<h1>Курси</h1>
@foreach($courses as $course)
<div>
    @if($course->getFirstMediaUrl('cover'))
        <img src="{{ $course->getFirstMediaUrl('cover') }}" alt="{{ $course->title }}" width="200">
    @endif
    <h2><a href="{{ route('courses.detail', $course) }}">{{ $course->title }}</a></h2>
    <p>{{ Str::limit($course->description, 150) }}</p>
    <p>Викладач: {{ $course->teacher->full_name }}</p>
    <p>Ціна: {{ $course->price }} грн/{{ ['monthly' => 'міс', 'one_time' => 'разово', 'per_lesson' => 'заняття'][$course->billing_period] ?? $course->billing_period }}</p>
    <p>Статус: {{ $course->status }}</p>
</div>
<hr>
@endforeach
{{ $courses->links() }}
@endsection
