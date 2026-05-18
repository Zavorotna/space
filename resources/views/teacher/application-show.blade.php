@extends('layouts.app')
@section('title', 'Заявка на курс')
@section('content')

<a href="{{ route('dashboard') }}">&larr; Дашборд</a>
<h1>Заявка на курс «{{ $application->course->title }}»</h1>

<div style="background:#f8f8f8;padding:16px;border-radius:8px;margin-bottom:24px;">
    <p><strong>Студент:</strong> {{ $application->user->full_name }}</p>
    <p><strong>Email:</strong> {{ $application->user->email }}</p>
    <p><strong>Телефон:</strong> {{ $application->user->phone ?? '—' }}</p>
    @if($application->note)
    <p><strong>Коментар:</strong> {{ $application->note }}</p>
    @endif
    <p><strong>Дата заявки:</strong> {{ $application->created_at->format('d.m.Y H:i') }}</p>
</div>

@if($application->status !== 'pending')
<div style="padding:12px;background:#e8f5e9;border-radius:8px;">
    Заявка вже оброблена — статус: <strong>{{ $application->status }}</strong>
</div>
@else

{{-- === Приєднати до існуючого курсу === --}}
@if($existingCourses->count())
<h2>Приєднати до існуючого курсу</h2>
<form method="POST" action="{{ route('applications.joinExisting', $application) }}">
    @csrf
    <div class="form-group">
        <label>Оберіть курс</label>
        <select name="course_id" required>
            <option value="">— Оберіть —</option>
            @foreach($existingCourses as $c)
            <option value="{{ $c->id }}">
                {{ $c->title }}
                @if($c->start_date) ({{ $c->start_date->format('d.m.Y') }}) @endif
                — {{ $c->activeStudents->count() }} студ.
            </option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Приєднати до курсу</button>
</form>
<hr>
@endif

{{-- === Створити новий курс === --}}
<h2>Створити новий курс</h2>
<p class="text-muted">Курс буде створено на основі шаблону «{{ $application->course->title }}», а студента додано автоматично.</p>
<form method="POST" action="{{ route('applications.createCourse', $application) }}"
      onsubmit="return confirm('Створити новий курс і додати студента?')">
    @csrf
    <button type="submit" class="btn btn-primary">Створити курс і додати студента</button>
</form>
<hr>

{{-- === Зберегти в замітки === --}}
<h2>Зберегти в замітки</h2>
<p class="text-muted">Якщо курс поки не проводиться — збережіть заявку в замітки. Сповіщення зникне.</p>
<form method="POST" action="{{ route('applications.saveNotes', $application) }}">
    @csrf
    <button type="submit" class="btn btn-ghost">Зберегти в замітки</button>
</form>

@endif

@endsection