@extends('layouts.app')
@section('title', 'Здача ДЗ: ' . $homework->title)

@section('content')
<a href="{{ route('courses.student.show', $homework->course_id) }}">&larr; Назад до курсу</a>

<h1>{{ $homework->title }}</h1>

<div>
    <p><strong>Складність:</strong>
        {{ ['easy' => 'Легка (+5)', 'medium' => 'Середня (+15)', 'hard' => 'Важка (+25)'][$homework->difficulty] }}
    </p>
    <p><strong>Дедлайн:</strong> {{ \Carbon\Carbon::parse($homework->deadline)->format('d.m.Y') }}</p>
    @if($homework->description)
        <div>
            <h3>Завдання</h3>
            {!! nl2br(e($homework->description)) !!}
        </div>
    @endif

    {{-- Homework attachments --}}
    @if($homework->getMedia('attachments')->count())
        <h3>Прикріплені файли</h3>
        <ul>
        @foreach($homework->getMedia('attachments') as $media)
            <li><a href="{{ $media->getUrl() }}" target="_blank">{{ $media->file_name }}</a></li>
        @endforeach
        </ul>
    @endif
</div>

<hr>

{{-- Current submission status --}}
@if($submission && $submission->exists)
    <div>
        <h3>Поточний статус</h3>
        <p>Статус:
            @switch($submission->status)
                @case('submitted') На перевірці @break
                @case('accepted') ✅ Прийнято @break
                @case('revision') ⚠️ На доопрацювання @break
                @default {{ $submission->status }}
            @endswitch
        </p>

        @if($submission->teacher_comment)
            <p><strong>Коментар викладача:</strong> {{ $submission->teacher_comment }}</p>
        @endif

        @if($submission->revision_count > 0)
            <p>Кількість доопрацювань: {{ $submission->revision_count }}</p>
        @endif

        {{-- Existing files --}}
        @if($submission->getMedia('files')->count())
            <h4>Завантажені файли</h4>
            @foreach($submission->getMedia('files') as $media)
                <div>
                    <img src="{{ $media->getUrl() }}" alt="{{ $media->file_name }}" style="max-width:200px;">
                </div>
            @endforeach
        @endif

        {{-- Deadline freeze --}}
        @if($submission->status !== 'accepted')
        <div>
            <h4>Заморозка дедлайну</h4>
            <p>Ефективний дедлайн: {{ $submission->effective_deadline ? \Carbon\Carbon::parse($submission->effective_deadline)->format('d.m.Y') : \Carbon\Carbon::parse($homework->deadline)->format('d.m.Y') }}</p>
            <p>Використано днів заморозки: {{ $submission->frozen_days ?? 0 }} / 5</p>
            <form method="POST" action="{{ route('homework.freeze', $submission) }}" style="display:inline;">
                @csrf
                <label>Днів:
                    <select name="days">
                        @for($d = 1; $d <= 5 - ($submission->frozen_days ?? 0); $d++)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endfor
                    </select>
                </label>
                <button type="submit">Заморозити (-15 монет/день)</button>
            </form>
        </div>
        @endif
    </div>
@endif

{{-- Submission form (if not accepted) --}}
@if(!$submission || $submission->status !== 'accepted')
<hr>
<h2>{{ $submission && $submission->exists ? 'Повторна здача' : 'Здати домашку' }}</h2>

<form method="POST" action="{{ route('homework.submit', $homework) }}" enctype="multipart/form-data">
    @csrf

    <div>
        <label>Завантаження файлу</label>
        <p>Формат: PNG, JPEG, WebP. Максимальний розмір: 2 МБ</p>
        <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp">
    </div>

    <div>
        <label>Посилання (кожне з нового рядка)</label>
        <textarea name="links" rows="3" placeholder="https://docs.google.com/spreadsheets/d/...">{{ old('links', $submission?->links ? implode("\n", $submission->links) : '') }}</textarea>
    </div>

    <div>
        <label>Або одне посилання</label>
        <input type="url" name="link_url" placeholder="https://...">
    </div>

    <button type="submit">Відправити</button>
</form>
@endif
@endsection
