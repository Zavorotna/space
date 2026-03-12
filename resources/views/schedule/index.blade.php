@extends('layouts.app')
@section('title', 'Розклад занять')
@section('content')
<h1>Розклад занять</h1>

<div>
    <a href="{{ route('schedule.index', ['mode' => 'day', 'date' => $date]) }}" @if($mode==='day') style="font-weight:bold" @endif>день</a>
    <a href="{{ route('schedule.index', ['mode' => 'week', 'date' => $date]) }}" @if($mode==='week') style="font-weight:bold" @endif>тиждень</a>
    <a href="{{ route('schedule.index', ['mode' => 'month', 'date' => $date]) }}" @if($mode==='month') style="font-weight:bold" @endif>місяць</a>
    <span>{{ \Carbon\Carbon::parse($date)->translatedFormat('F Y') }}</span>
</div>

@if($mode === 'day')
    <h2>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F') }}</h2>
    @forelse($lessons as $lesson)
    <div>
        <strong>{{ $lesson->start_time }} - {{ $lesson->end_time }}</strong>
        {{ $lesson->course->title }}
        {{ $lesson->title ? "({$lesson->title})" : '' }}
        [{{ $lesson->mode }}]
        @if($lesson->location) | {{ $lesson->location->name }} @endif
    </div>
    @empty
    <p>Немає занять на цей день.</p>
    @endforelse
@elseif($mode === 'week')
    @php $grouped = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d')); @endphp
    @for($d = \Carbon\Carbon::parse($date)->startOfWeek(); $d <= \Carbon\Carbon::parse($date)->endOfWeek(); $d->addDay())
        <h3>{{ $d->translatedFormat('D d.m') }}</h3>
        @foreach($grouped->get($d->format('Y-m-d'), collect()) as $lesson)
        <div>
            {{ $lesson->start_time }} - {{ $lesson->end_time }}
            | {{ $lesson->course->title }}
            @if($lesson->mode === 'offline' && $lesson->location) | {{ $lesson->location->name }} @endif
        </div>
        @endforeach
    @endfor
@elseif($mode === 'month')
    @php
        $monthStart = \Carbon\Carbon::parse($date)->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse($date)->endOfMonth();
        $grouped = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d'));
    @endphp
    <table>
        <thead><tr><th>ПН</th><th>ВТ</th><th>СР</th><th>ЧТ</th><th>ПТ</th><th>СБ</th><th>НД</th></tr></thead>
        <tbody>
        @php $current = $monthStart->copy()->startOfWeek(); @endphp
        @while($current <= $monthEnd->copy()->endOfWeek())
        <tr>
            @for($i = 0; $i < 7; $i++)
                <td>
                    {{ $current->day }}
                    @foreach($grouped->get($current->format('Y-m-d'), collect()) as $lesson)
                        <div>{{ $lesson->start_time }} {{ Str::limit($lesson->course->title, 10) }}</div>
                    @endforeach
                </td>
                @php $current->addDay(); @endphp
            @endfor
        </tr>
        @endwhile
        </tbody>
    </table>
@endif

@if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
<hr>
<h2>Додати заняття</h2>
<form method="POST" action="{{ route('teacher.schedule.store') }}">
    @csrf
    <select name="course_id" required>
        <option value="">Оберіть курс</option>
        @foreach(auth()->user()->isTeacher() ? auth()->user()->taughtCourses : \App\Models\Course::active()->get() as $c)
            <option value="{{ $c->id }}">{{ $c->title }}</option>
        @endforeach
    </select>
    <input type="text" name="title" placeholder="Тема заняття">
    <select name="mode">
        <option value="offline">Офлайн</option>
        <option value="online">Онлайн</option>
    </select>
    <select name="location_id">
        <option value="">Локація</option>
        @foreach($locations as $loc)
            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
        @endforeach
    </select>
    <select name="classroom_id">
        <option value="">Аудиторія</option>
        @foreach($locations as $loc)
            @foreach($loc->classrooms as $room)
                <option value="{{ $room->id }}">{{ $loc->name }} — {{ $room->name }}</option>
            @endforeach
        @endforeach
    </select>
    <input type="date" name="date" required>
    <input type="time" name="start_time" required>
    <input type="time" name="end_time" required>
    <button type="submit">Додати заняття</button>
</form>
@endif

@foreach($lessons->where('attendance_confirmed', false) as $lesson)
    @if(auth()->user()->isTeacher() && $lesson->teacher_id === auth()->id() && $lesson->date <= today())
    <div>
        <h3>Присутність: {{ $lesson->course->title }} ({{ $lesson->date->format('d.m') }})</h3>
        <form method="POST" action="{{ route('teacher.schedule.attendance', $lesson) }}">
            @csrf
            @foreach($lesson->course->activeStudents as $student)
            <div>
                <label>
                    <input type="checkbox" name="present_students[]" value="{{ $student->id }}" checked>
                    {{ $student->full_name }}
                </label>
            </div>
            @endforeach
            <button type="submit">Підтвердити присутність</button>
        </form>
    </div>
    @endif
@endforeach
@endsection
