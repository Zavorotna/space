@extends('layouts.app')
@section('title', 'Замітки')

@section('content')
<h1>Замітки</h1>

{{-- Create personal note --}}
<h2>Нова замітка</h2>
<form method="POST" action="{{ route('notes.store') }}">
    @csrf
    <div>
        <textarea name="content" rows="3" placeholder="Текст замітки..." required></textarea>
    </div>

    {{-- Teacher can send to student --}}
    @if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
    <div>
        <label>Надіслати студенту (необов'язково)</label>
        <input type="number" name="recipient_id" placeholder="ID студента">
    </div>
    <div>
        <label>Курс (необов'язково)</label>
        <select name="course_id">
            <option value="">— без курсу —</option>
            @foreach(auth()->user()->taughtCourses ?? [] as $course)
                <option value="{{ $course->id }}">{{ $course->title }}{{ $course->is_template ? ' (шаблон)' : '' }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <button type="submit">Зберегти</button>
</form>

<hr>

{{-- Received notes (from teacher) --}}
@if($receivedNotes->count())
<h2>Замітки від викладача</h2>
@foreach($receivedNotes as $note)
    <div class="card {{ !$note->is_read ? 'card--unread' : '' }}">
        <p><strong>{{ $note->author->last_name ?? '' }} {{ $note->author->first_name ?? '' }}</strong>
            — {{ $note->created_at->format('d.m.Y H:i') }}</p>
        <p>{!! nl2br(e($note->content)) !!}</p>
        @if(!$note->is_read)
            <form method="POST" action="{{ route('notes.read', $note) }}" class="form-inline">
                @csrf
                <button type="submit">Прочитано</button>
            </form>
        @endif
    </div>
@endforeach
@endif

{{-- Personal notes --}}
<h2>Мої замітки</h2>
@if($personalNotes->isEmpty())
    <p>У вас ще немає заміток.</p>
@else
    @foreach($personalNotes as $note)
    <div class="card">
        <p>{{ $note->created_at->format('d.m.Y H:i') }}</p>
        <form method="POST" action="{{ route('notes.update', $note) }}" class="form-inline">
            @csrf @method('PUT')
            <textarea name="content" rows="2">{{ $note->content }}</textarea>
            <button type="submit">Оновити</button>
        </form>
        <form method="POST" action="{{ route('notes.destroy', $note) }}" class="form-inline"
              onsubmit="return confirm('Видалити?')">
            @csrf @method('DELETE')
            <button type="submit">Видалити</button>
        </form>
    </div>
    @endforeach
@endif
@endsection
