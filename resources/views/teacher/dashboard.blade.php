@extends('layouts.app')
@section('title', 'Дашборд викладача')
@section('content')
<h1>Дашборд</h1>

@include('partials._admin_banners')

{{-- ── Calendar ── --}}
@include('partials._calendar', [
    'schedDate'      => $schedDate,
    'schedMode'      => $schedMode,
    'schedLessons'   => $schedLessons,
    'schedEvents'    => $schedEvents,
    'schedLocations' => $schedLocations,
    'schedCourses'   => $schedCourses,
    'schedBirthdays' => $schedBirthdays,
    'schedNotes'     => $schedNotes,
    'canEdit'        => true,
])

{{-- Lesson completion reports --}}
@include('partials._lessons_needing_report', ['lessonsNeedingReport' => $lessonsNeedingReport])

<h2>Курси</h2>
@foreach($courses as $course)
<div class="mb-1">
    <strong>{{ $course->title }}</strong>
    @php
        $progress = 0;
        if ($course->start_date && $course->end_date) {
            $total   = $course->start_date->diffInDays($course->end_date);
            $elapsed = $course->start_date->diffInDays(now());
            $progress = $total > 0 ? min(100, round($elapsed / $total * 100)) : 0;
        }
    @endphp
    {{ $progress }}%
    <progress value="{{ $progress }}" max="100"></progress>
    <span class="text-sm text-muted">{{ $course->start_date?->format('d.m') }} — {{ $course->end_date?->format('d.m') }}</span>
    @if($course->applications()->where('status','pending')->count() > 0)
    <a href="{{ route('teacher.courses.applications', $course) }}" class="text-sm">
        {{ $course->applications()->where('status','pending')->count() }} заявок
    </a>
    @endif
</div>
@endforeach

@if($pendingHomework > 0)
<p>Домашок на перевірку: <strong>{{ $pendingHomework }}</strong></p>
@endif

<hr>
<h2>Мої замітки</h2>
@forelse($notes as $note)
<div style="padding:10px; background:#fff8e1; margin:5px 0; border-radius:4px; border-left:4px solid #ffc107;">
    <div style="font-size:0.9em; color:#666;">
        @if($note->reminder_time)
            📅 {{ $note->reminder_time->format('d.m.Y H:i') }}
        @else
            📝 {{ $note->created_at->format('d.m.Y H:i') }}
        @endif
    </div>
    <p style="margin:5px 0;">{{ $note->content }}</p>
    <div style="font-size:0.85em;">
        <form method="POST" action="{{ route('notes.destroy', $note) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('Видалити замітку?')">Видалити</button>
        </form>
    </div>
</div>
@empty
<p class="text-subtle">Немає особистих заміток.</p>
@endforelse

<button type="button" class="btn btn-ghost mt-1" onclick="this.style.display='none';document.getElementById('note-form-teacher').style.display='block'">+ Додати замітку</button>
<form id="note-form-teacher" method="POST" action="{{ route('notes.store') }}" style="display:none;">
    @csrf
    <div class="form-group">
        <label>Замітка</label>
        <textarea name="content" placeholder="Текст замітки..." required></textarea>
    </div>
    <div class="form-group">
        <label>Нагадування (опціонально)</label>
        <input type="datetime-local" name="reminder_time">
    </div>
    <div style="display:flex;gap:8px;">
        <button type="submit" class="btn btn-primary">Зберегти</button>
        <button type="button" class="btn btn-ghost" onclick="this.closest('form').style.display='none';document.querySelector('[onclick*=note-form-teacher]').style.display=''">Скасувати</button>
    </div>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong>{{ $wallet->balance }}</strong></p>
<a href="{{ route('wallet.transfer') }}">переказати</a>
<a href="{{ route('wallet.topup') }}">поповнити</a>
<a href="{{ route('wallet.withdraw') }}">вивести</a>

<h3>Транзакції</h3>
<table class="data-table">
    <thead><tr><th>Дата</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    @foreach($transactions as $tx)
    <tr>
        <td>{{ $tx->created_at->format('d.m.y') }}</td>
        <td>{{ $tx->description }}</td>
        <td>{{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection