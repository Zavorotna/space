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
    'canEdit'        => true,
])

{{-- Lesson completion reports --}}
@if($lessonsNeedingReport->count() > 0)
<div class="report-section">
    <h2>Потрібен звіт ({{ $lessonsNeedingReport->count() }})</h2>
    @foreach($lessonsNeedingReport as $lesson)
    @if($lesson->course)
    @php $isIndividual = $lesson->course->type === 'individual'; @endphp
    <div class="report-item">
        <strong>{{ $lesson->date->format('d.m.Y') }}</strong>
        · {{ $lesson->course->title }}
        {{ $lesson->title ? "· {$lesson->title}" : '' }}
        · {{ $lesson->start_time }}–{{ $lesson->end_time }}
        ({{ $lesson->plannedMinutes() }} хв)
        <span class="text-sm text-muted">{{ $isIndividual ? 'Індивідуальне' : 'Групове' }}</span>

        <form method="POST" action="{{ route('teacher.schedule.complete', $lesson) }}" class="mt-1">
            @csrf
            <div class="flex-row flex-start">
                <div>
                    <label>Статус</label><br>
                    @if($isIndividual)
                        <select name="completion_status" required id="status-{{ $lesson->id }}"
                                onchange="toggleFields({{ $lesson->id }}, this.value, true)">
                            <option value="full" selected>Повне заняття</option>
                            <option value="partial">Часткове</option>
                            <option value="cancelled">Скасовано</option>
                        </select>
                    @else
                        <select name="completion_status" required id="status-{{ $lesson->id }}"
                                onchange="toggleFields({{ $lesson->id }}, this.value, false)">
                            <option value="full" selected>Повне заняття</option>
                            <option value="cancelled">Скасовано</option>
                            <option value="rescheduled">Перенесено</option>
                        </select>
                    @endif
                </div>
                @if($isIndividual)
                <div id="minutes-{{ $lesson->id }}" style="display:none;">
                    <label>Фактично годин</label><br>
                    <input type="number" name="actual_hours" min="0.5" max="10" step="0.5"
                           placeholder="{{ round($lesson->plannedMinutes() / 60, 1) }}" class="input-sm">
                </div>
                @endif
                <div>
                    <label>Примітка</label><br>
                    <input type="text" name="completion_note" placeholder="необов'язково" class="input-md">
                </div>
            </div>
            <div id="makeup-{{ $lesson->id }}" class="makeup-panel" style="display:none;">
                <label><input type="checkbox" name="schedule_makeup" value="1"
                              id="makeup-cb-{{ $lesson->id }}"
                              onchange="toggleMakeupDate({{ $lesson->id }})">
                    Запланувати відпрацювання</label>
                <div id="makeup-date-{{ $lesson->id }}" class="makeup-date-row" style="display:none;">
                    <input type="date" name="makeup_date">
                    <input type="time" name="makeup_start">
                    <input type="time" name="makeup_end">
                </div>
            </div>
            <button type="submit" class="btn mt-1">Зберегти звіт</button>
        </form>
    </div>
    @endif
    @endforeach
</div>
<script>
function toggleFields(id, status, isIndividual) {
    const minutesEl = document.getElementById('minutes-' + id);
    const makeupEl  = document.getElementById('makeup-' + id);
    if (minutesEl) minutesEl.style.display = (status === 'partial') ? 'block' : 'none';
    if (makeupEl)  makeupEl.style.display  = (status === 'cancelled' || status === 'rescheduled') ? 'block' : 'none';
}
function toggleMakeupDate(id) {
    const cb = document.getElementById('makeup-cb-' + id);
    document.getElementById('makeup-date-' + id).style.display = cb.checked ? 'flex' : 'none';
}
</script>
@endif

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