@extends('layouts.app')
@section('title', 'Редагування резюме')

@section('content')
<h1>Редагування резюме</h1>

<form method="POST" action="{{ route('resume.update') }}">
    @csrf @method('PUT')

    <div>
        <label>Про себе</label>
        <textarea name="about" rows="4">{{ old('about', $resume->about) }}</textarea>
    </div>

    <div>
        <label>Досвід роботи</label>
        <textarea name="work_experience" rows="4">{{ old('work_experience', $resume->work_experience) }}</textarea>
    </div>

    <div>
        <label>Посилання на проєкти (кожне з нового рядка)</label>
        <textarea name="project_links_text" rows="3" id="project-links">{{ old('project_links_text', $resume->project_links ? implode("\n", $resume->project_links) : '') }}</textarea>
    </div>

    <div>
        <label>Email для зв'язку</label>
        <input type="email" name="contact_email" value="{{ old('contact_email', $resume->contact_email) }}">
    </div>

    <div>
        <label>Телефон для зв'язку</label>
        <input type="text" name="contact_phone" value="{{ old('contact_phone', $resume->contact_phone) }}">
    </div>

    {{-- Hide courses --}}
    @if($courses->count())
    <div>
        <h3>Курси у резюме</h3>
        <p>Зніміть галочку, щоб приховати невдалий курс:</p>
        @foreach($courses as $course)
            <label>
                <input type="checkbox" name="hidden_courses[]" value="{{ $course->id }}"
                    @checked(in_array($course->id, $resume->hidden_courses ?? []))>
                Приховати: {{ $course->title }}
            </label><br>
        @endforeach
    </div>
    @endif

    <div>
        <label>
            <input type="checkbox" name="has_offer" value="1" @checked($resume->has_offer)>
            Отримав офер (вимкнути публікацію)
        </label>
    </div>

    <button type="submit">Зберегти</button>
</form>

<hr>

{{-- Publish --}}
<h2>Публікація</h2>
@if($resume->is_published)
    <p>Резюме опубліковано. <a href="{{ route('resumes.show', $resume) }}">Переглянути</a></p>
@else
    <form method="POST" action="{{ route('resume.publish') }}">
        @csrf
        @if(auth()->user()->isVip())
            <p>Публікація безкоштовна (VIP)</p>
        @else
            <p>Вартість: 100 монет на 1 рік</p>
        @endif
        <button type="submit">Опублікувати резюме</button>
    </form>
@endif
@endsection
