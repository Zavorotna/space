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

<div class="sched-tabs">
    <a href="{{ route('schedule.index', ['mode' => 'day',   'date' => $date]) }}" @class(['active' => $mode==='day'])>День</a>
    <a href="{{ route('schedule.index', ['mode' => 'week',  'date' => $date]) }}" @class(['active' => $mode==='week'])>Тиждень</a>
    <a href="{{ route('schedule.index', ['mode' => 'month', 'date' => $date]) }}" @class(['active' => $mode==='month'])>Місяць</a>
</div>

<div class="sched-nav">
    <a href="{{ route('schedule.index', ['mode' => $mode, 'date' => $prevDate]) }}">&larr;</a>
    <strong>{{ $periodLabel }}</strong>
    <a href="{{ route('schedule.index', ['mode' => $mode, 'date' => $nextDate]) }}">&rarr;</a>
    <a href="{{ route('schedule.index', ['mode' => $mode, 'date' => today()->toDateString()]) }}" class="text-sm">Сьогодні</a>
</div>

{{-- DAY VIEW --}}
@if($mode === 'day')
    @forelse($lessons as $lesson)
    <div class="sched-lesson">
        <div class="flex-between">
            <div>
                <strong>{{ substr($lesson->start_time,0,5) }} — {{ substr($lesson->end_time,0,5) }}</strong>
                · {{ $lesson->course->title }}
                {{ $lesson->title ? "· <em>{$lesson->title}</em>" : '' }}
                <span class="text-muted text-sm">[{{ $lesson->mode === 'online' ? 'Онлайн' : 'Офлайн' }}]</span>
                @if($lesson->location) · {{ $lesson->location->name }} @endif
            </div>
            @if(auth()->user()->isTeacher() || auth()->user()->isAdmin())
            <details class="sched-edit-details">
                <summary class="btn btn-xs btn-ghost">✏️</summary>
                <div class="sched-edit-form">
                    <form method="POST" action="{{ route('teacher.schedule.update', $lesson) }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Тема</label>
                            <input type="text" name="title" value="{{ $lesson->title }}" placeholder="Тема заняття">
                        </div>
                        <div class="flex-row">
                            <div><label>Дата</label><br><input type="date" name="date" value="{{ $lesson->date->format('Y-m-d') }}" required></div>
                            <div><label>Початок</label><br><input type="time" name="start_time" value="{{ substr($lesson->start_time,0,5) }}" required></div>
                            <div><label>Кінець</label><br><input type="time" name="end_time" value="{{ substr($lesson->end_time,0,5) }}" required></div>
                        </div>
                        <div class="flex-row mt-1">
                            <select name="mode">
                                <option value="online" @selected($lesson->mode==='online')>Онлайн</option>
                                <option value="offline" @selected($lesson->mode==='offline')>Офлайн</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Зберегти</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('teacher.schedule.destroy', $lesson) }}" class="mt-1"
                          onsubmit="return confirm('Видалити заняття?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger">Видалити</button>
                    </form>
                </div>
            </details>
            @endif
        </div>
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
    <div class="sched-week-day">
        <div class="mb-1">
            <a href="{{ route('schedule.index', ['mode' => 'day', 'date' => $key]) }}"
               @class(['active' => $key === today()->toDateString()])>
                {{ $d->translatedFormat('D d.m') }}
            </a>
        </div>
        @forelse($dayLessons as $lesson)
        <div class="sched-week-item">
            {{ $lesson->start_time }} — {{ $lesson->end_time }}
            · <strong>{{ $lesson->course->title }}</strong>
            {{ $lesson->title ? "· {$lesson->title}" : '' }}
            @if($lesson->mode === 'offline' && $lesson->location) · {{ $lesson->location->name }} @endif
        </div>
        @empty
        <span class="text-subtle text-sm">Немає занять</span>
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
    <table class="data-table">
        <thead>
            <tr>
                @foreach(['ПН','ВТ','СР','ЧТ','ПТ','СБ','НД'] as $day)
                    <th style="text-align:center;">{{ $day }}</th>
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
            <td style="vertical-align:top; height:50px; {{ !$isCurrentMonth ? 'color:#ccc;' : '' }}">
                @if($count > 0)
                    <a href="{{ route('schedule.index', ['mode' => 'day', 'date' => $key]) }}"
                       class="cal-mc-num cal-mc-num--link"
                       title="{{ $count }} {{ trans_choice('заняття|заняття|занять', $count) }}">
                        {{ $cell->day }}
                    </a>
                    <span class="text-muted text-xs">×{{ $count }}</span>
                @else
                    <span @class(['active' => $isToday])>{{ $cell->day }}</span>
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
        @php
            $u = auth()->user();
            $addCourses = $u->isAdmin()
                ? \App\Models\Course::where('is_template', false)->whereNotIn('status',['completed'])->orderBy('title')->get()
                : \App\Models\Course::where('is_template', false)->whereNotIn('status',['completed'])
                    ->where(fn($q) => $q->where('teacher_id', $u->id)->orWhereHas('coTeachers', fn($q2) => $q2->where('users.id', $u->id)))
                    ->orderBy('title')->get();
        @endphp
        @foreach($addCourses as $c)
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