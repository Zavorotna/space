@extends('layouts.app')
@section('title', 'Розклад занять')
@section('content')
<h1>Розклад занять</h1>

@php
    $current = \Carbon\Carbon::parse($date);

    $prevDate = match($mode) {
        'day'   => $current->copy()->subDay()->toDateString(),
        'week'  => $current->copy()->subWeek()->toDateString(),
        'month' => $current->copy()->subMonth()->toDateString(),
    };
    $nextDate = match($mode) {
        'day'   => $current->copy()->addDay()->toDateString(),
        'week'  => $current->copy()->addWeek()->toDateString(),
        'month' => $current->copy()->addMonth()->toDateString(),
    };

    $periodLabel = match($mode) {
        'day'   => $current->translatedFormat('l, d F Y'),
        'week'  => $current->copy()->startOfWeek()->format('d.m') . ' — ' . $current->copy()->endOfWeek()->format('d.m.Y'),
        'month' => $current->translatedFormat('F Y'),
    };
@endphp

{{-- Mode tabs --}}
<div>
    <a href="{{ route('schedule.index', ['mode' => 'day',   'date' => $date]) }}" @if($mode==='day')   style="font-weight:bold" @endif>День</a>
    <a href="{{ route('schedule.index', ['mode' => 'week',  'date' => $date]) }}" @if($mode==='week')  style="font-weight:bold" @endif>Тиждень</a>
    <a href="{{ route('schedule.index', ['mode' => 'month', 'date' => $date]) }}" @if($mode==='month') style="font-weight:bold" @endif>Місяць</a>
</div>

{{-- Navigation --}}
<div style="display:flex; align-items:center; gap:12px; margin:10px 0;">
    <a href="{{ route('schedule.index', ['mode' => $mode, 'date' => $prevDate]) }}">&larr;</a>
    <strong>{{ $periodLabel }}</strong>
    <a href="{{ route('schedule.index', ['mode' => $mode, 'date' => $nextDate]) }}">&rarr;</a>
    <a href="{{ route('schedule.index', ['mode' => $mode, 'date' => today()->toDateString()]) }}" style="font-size:0.85em;">Сьогодні</a>
</div>

{{-- DAY VIEW --}}
@if($mode === 'day')
    @forelse($lessons as $lesson)
    <div style="border:1px solid #ddd; padding:8px; margin:6px 0;">
        <strong>{{ $lesson->start_time }} — {{ $lesson->end_time }}</strong>
        {{ $lesson->course->title }}
        {{ $lesson->title ? "· {$lesson->title}" : '' }}
        <span style="color:#888;">[{{ $lesson->mode === 'online' ? 'Онлайн' : 'Офлайн' }}]</span>
        @if($lesson->location) · {{ $lesson->location->name }} @endif
        @if($lesson->classroom) ({{ $lesson->classroom->name }}) @endif
    </div>
    @empty
    <p>Немає занять на цей день.</p>
    @endforelse

{{-- WEEK VIEW --}}
@elseif($mode === 'week')
    @php $grouped = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d')); @endphp
    @php $weekStart = $current->copy()->startOfWeek(); @endphp
    @for($d = $weekStart->copy(); $d <= $weekStart->copy()->endOfWeek(); $d->addDay())
    @php $key = $d->format('Y-m-d'); $dayLessons = $grouped->get($key, collect()); @endphp
    <div style="margin-bottom:10px;">
        <div style="margin-bottom:4px;">
            <a href="{{ route('schedule.index', ['mode' => 'day', 'date' => $key]) }}"
               style="font-weight:{{ $key === today()->toDateString() ? 'bold' : 'normal' }};">
                {{ $d->translatedFormat('D d.m') }}
            </a>
        </div>
        @forelse($dayLessons as $lesson)
        <div style="padding:4px 0; border-bottom:1px solid #eee;">
            {{ $lesson->start_time }} — {{ $lesson->end_time }}
            · <strong>{{ $lesson->course->title }}</strong>
            {{ $lesson->title ? "· {$lesson->title}" : '' }}
            @if($lesson->mode === 'offline' && $lesson->location) · {{ $lesson->location->name }} @endif
        </div>
        @empty
        <span style="color:#aaa; font-size:0.85em;">Немає занять</span>
        @endforelse
    </div>
    @endfor

{{-- MONTH VIEW --}}
@elseif($mode === 'month')
    @php
        $monthStart = $current->copy()->startOfMonth();
        $monthEnd   = $current->copy()->endOfMonth();
        $grouped    = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d'));
        $cell       = $monthStart->copy()->startOfWeek();
    @endphp
    <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <thead>
            <tr>
                @foreach(['ПН','ВТ','СР','ЧТ','ПТ','СБ','НД'] as $day)
                    <th style="padding:6px; border:1px solid #ddd; text-align:center;">{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @while($cell <= $monthEnd->copy()->endOfWeek())
        <tr>
            @for($i = 0; $i < 7; $i++)
            @php
                $key = $cell->format('Y-m-d');
                $count = $grouped->get($key, collect())->count();
                $isCurrentMonth = $cell->month === $monthStart->month;
                $isToday = $key === today()->toDateString();
            @endphp
            <td style="padding:6px; border:1px solid #ddd; vertical-align:top; height:50px;
                       {{ !$isCurrentMonth ? 'color:#ccc;' : '' }}">
                @if($count > 0)
                    <a href="{{ route('schedule.index', ['mode' => 'day', 'date' => $key]) }}"
                       style="display:inline-flex; align-items:center; justify-content:center;
                              width:26px; height:26px; border-radius:50%; background:#4a90d9;
                              color:#fff; text-decoration:none; font-weight:bold; font-size:0.85em;"
                       title="{{ $count }} {{ trans_choice('заняття|заняття|занять', $count) }}">
                        {{ $cell->day }}
                    </a>
                    <span style="font-size:0.75em; color:#888;">×{{ $count }}</span>
                @else
                    <span style="{{ $isToday ? 'font-weight:bold;' : '' }}">{{ $cell->day }}</span>
                @endif
            </td>
            @php $cell->addDay(); @endphp
            @endfor
        </tr>
        @endwhile
        </tbody>
    </table>
@endif

{{-- Add lesson form --}}
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
    <input type="date" name="date" value="{{ $mode === 'day' ? $date : '' }}" required>
    <input type="time" name="start_time" required>
    <input type="time" name="end_time" required>
    <button type="submit">Додати заняття</button>
</form>
@endif

{{-- Attendance --}}
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