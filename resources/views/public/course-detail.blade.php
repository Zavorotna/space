@extends('layouts.app')
@section('title', $course->title)
@section('content')

<a href="{{ route('courses.public') }}">&larr; Всі курси</a>

<h1>{{ $course->title }}</h1>

@if($course->getFirstMediaUrl('cover'))
<img src="{{ $course->getFirstMediaUrl('cover') }}" alt="{{ $course->title }}" style="max-width:100%;border-radius:8px;margin-bottom:16px;">
@endif

@if($course->description)
<p>{{ $course->description }}</p>
@endif

@if($course->topics->count())
<h2>Програма курсу</h2>
<ol>
    @foreach($course->topics as $topic)
    <li>{{ $topic->title }}</li>
    @endforeach
</ol>
@endif

<div style="margin:20px 0;padding:16px;background:#f8f8f8;border-radius:8px;">
    <strong>Вартість:</strong>
    {{ number_format($course->price, 0, '.', ' ') }} грн
    / {{ ['monthly' => 'місяць', 'one_time' => 'разово', 'per_lesson' => 'заняття'][$course->billing_period] ?? '' }}
</div>

@if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
{{-- Teachers and admins don't apply to courses --}}
@elseif($isEnrolled)
<div style="padding:14px;background:#e8f5e9;border-radius:8px;margin:16px 0;">
    ✅ Ви вже записані на цей курс.
</div>
@elseif($hasApplication)
<div style="padding:14px;background:#e8f5e9;border-radius:8px;margin:16px 0;">
    ✅ Вашу заявку прийнято. Ми зв'яжемось з вами найближчим часом.
</div>
@elseif(!auth()->user()->phone)
<div style="padding:14px;background:#fff3e0;border-radius:8px;margin:16px 0;">
    ⚠️ Для подачі заявки вкажіть <a href="{{ route('profile.edit') }}">номер телефону у профілі</a>.
</div>
@else
<div style="margin:20px 0;">
    <h2>Подати заявку</h2>
    <form method="POST" action="{{ route('courses.apply', $course) }}">
        @csrf
        <div class="form-group">
            <label>Коментар (необов'язково)</label>
            <textarea name="note" rows="3" placeholder="Розкажіть трохи про себе, ваш досвід..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Подати заявку</button>
    </form>
</div>
@endif

@if($course->reviews->count())
<h2>Відгуки</h2>
@foreach($course->reviews as $review)
<div style="padding:10px;border-bottom:1px solid #eee;">
    <strong>{{ $review->user->full_name }}</strong>
    <span style="color:#f5a623;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
    <p>{{ $review->text }}</p>
</div>
@endforeach
@endif

@endsection