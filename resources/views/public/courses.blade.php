@extends('layouts.app')
@section('title', 'Курси')
@section('content')
<h1>Курси</h1>
<p class="text-muted">Оберіть напрям — подайте заявку — ми підберемо зручний час і групу.</p>

@forelse($courses as $course)
<div class="course-card">
    @if($course->getFirstMediaUrl('cover'))
    <img src="{{ $course->getFirstMediaUrl('cover') }}" alt="{{ $course->title }}" class="course-card__img">
    @endif
    <div class="course-card__body">
        <h2 class="course-card__title">
            <a href="{{ route('courses.detail', $course) }}">{{ $course->title }}</a>
        </h2>
        @if($course->description)
        <p class="course-card__desc">{{ Str::limit($course->description, 150) }}</p>
        @endif
        <div class="course-card__meta">
            <span>{{ number_format($course->price, 0, '.', ' ') }} грн
                / {{ ['monthly' => 'місяць', 'one_time' => 'разово', 'per_lesson' => 'заняття'][$course->billing_period] ?? '' }}
            </span>
        </div>
        <a href="{{ route('courses.detail', $course) }}" class="btn btn-sm">Детальніше</a>
    </div>
</div>
@empty
<p>Наразі немає доступних курсів.</p>
@endforelse

{{ $courses->links() }}
@endsection