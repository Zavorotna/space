@extends('layouts.app')
@section('title', 'Дашборд викладача')
@section('content')
<h1>Дашборд</h1>

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
@if($lessonsNeedingReport->count())
<div style="border:2px solid #e67e22;padding:15px;margin:15px 0;border-radius:8px;">
    <h2 style="color:#e67e22;margin-top:0;">Потрібен звіт ({{ $lessonsNeedingReport->count() }})</h2>
    @foreach($lessonsNeedingReport as $lesson)
    @php $isIndividual = $lesson->course->type === 'individual'; @endphp
    <div style="border:1px solid #ddd;padding:10px;margin:8px 0;border-radius:4px;">
        <strong>{{ $lesson->date->format('d.m.Y') }}</strong>
        · {{ $lesson->course->title }}
        {{ $lesson->title ? "· {$lesson->title}" : '' }}
        · {{ $lesson->start_time }}–{{ $lesson->end_time }}
        ({{ $lesson->plannedMinutes() }} хв)
        <span style="color:#888;font-size:.85em;">{{ $isIndividual ? 'Індивідуальне' : 'Групове' }}</span>

        <form method="POST" action="{{ route('teacher.schedule.complete', $lesson) }}" style="margin-top:8px;">
            @csrf
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;">
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
                           placeholder="{{ round($lesson->plannedMinutes() / 60, 1) }}" style="width:70px;">
                </div>
                @endif
                <div>
                    <label>Примітка</label><br>
                    <input type="text" name="completion_note" placeholder="необов'язково" style="width:200px;">
                </div>
            </div>
            <div id="makeup-{{ $lesson->id }}" style="display:none;margin-top:8px;padding:8px;background:#fff8e1;border-radius:4px;">
                <label><input type="checkbox" name="schedule_makeup" value="1"
                              id="makeup-cb-{{ $lesson->id }}"
                              onchange="toggleMakeupDate({{ $lesson->id }})">
                    Запланувати відпрацювання</label>
                <div id="makeup-date-{{ $lesson->id }}" style="display:none;margin-top:6px;">
                    <input type="date" name="makeup_date" style="margin-right:4px;">
                    <input type="time" name="makeup_start" style="margin-right:4px;">
                    <input type="time" name="makeup_end">
                </div>
            </div>
            <button type="submit" style="margin-top:8px;">Зберегти звіт</button>
        </form>
    </div>
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
    document.getElementById('makeup-date-' + id).style.display = cb.checked ? 'block' : 'none';
}
</script>
@endif

<h2>Курси</h2>
@foreach($courses as $course)
<div style="margin-bottom:8px;">
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
    <span style="font-size:.85em;color:#888;">{{ $course->start_date?->format('d.m') }} — {{ $course->end_date?->format('d.m') }}</span>
    @if($course->applications()->where('status','pending')->count() > 0)
    <a href="{{ route('teacher.courses.applications', $course) }}" style="font-size:.85em;">
        {{ $course->applications()->where('status','pending')->count() }} заявок
    </a>
    @endif
</div>
@endforeach

@if($pendingHomework > 0)
<p>Домашок на перевірку: <strong>{{ $pendingHomework }}</strong></p>
@endif

<h2>Замітки</h2>
@foreach($notes as $note)
<div>{{ $note->content }}</div>
@endforeach
<form method="POST" action="{{ route('notes.store') }}">
    @csrf
    <textarea name="content" placeholder="Нова замітка..." required></textarea>
    <button type="submit">Зберегти</button>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong>{{ $wallet->balance }}</strong></p>
<a href="{{ route('wallet.transfer') }}">переказати</a>
<a href="{{ route('wallet.topup') }}">поповнити</a>
<a href="{{ route('wallet.withdraw') }}">вивести</a>

<h3>Транзакції</h3>
<table>
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
