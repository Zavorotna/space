@extends('layouts.app')
@section('title', 'Адмін панель')
@section('content')
<h1>Адмін панель</h1>

@include('partials._admin_banners')

@if($pendingApplications > 0)
<p class="mb-1">
    Заявок на розгляді: <strong>{{ $pendingApplications }}</strong>
</p>
@endif

@if(auth()->user()->isSuperAdmin() && $pendingWithdrawalsList->count() > 0)
<div class="withdrawal-section">
    <h2>Запити на виведення ({{ $pendingWithdrawalsList->count() }})</h2>
    @foreach($pendingWithdrawalsList as $req)
    <div class="withdrawal-item">
        <div>
            <strong>{{ $req->user->last_name }} {{ $req->user->first_name }}</strong><br>
            <span class="text-sm text-muted">{{ $req->amount }} монет · {{ $req->created_at->format('d.m.Y H:i') }}</span>
        </div>
        <form method="POST" action="{{ route('superadmin.withdrawals.approve', $req) }}" class="flex-row">
            @csrf
            <input type="text" name="pickup_note" placeholder="Куди підійти" required class="input-w-180">
            <button type="submit" class="btn btn-sm btn-success">Видати готівку</button>
        </form>
        <form method="POST" action="{{ route('superadmin.withdrawals.reject', $req) }}"
              onsubmit="return confirm('Відхилити? Монети повернуться.')">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">Відхилити</button>
        </form>
    </div>
    @endforeach
</div>
@endif

{{-- ── Lesson completion reports ── --}}
@if($lessonsNeedingReport->count())
<div class="report-section">
    <h2>Потрібен звіт ({{ $lessonsNeedingReport->count() }})</h2>
    @foreach($lessonsNeedingReport as $lesson)
    @php $isIndividual = $lesson->course->type === 'individual'; @endphp
    <div class="report-item">
        <strong>{{ $lesson->date->format('d.m.Y') }}</strong>
        · {{ $lesson->course->title }}
        {{ $lesson->title ? "· {$lesson->title}" : '' }}
        · {{ substr($lesson->start_time,0,5) }}–{{ substr($lesson->end_time,0,5) }}
        ({{ $lesson->plannedMinutes() }} хв)
        <span class="text-sm text-muted">{{ $isIndividual ? 'Індивідуальне' : 'Групове' }}</span>

        <form method="POST" action="{{ route('teacher.schedule.complete', $lesson) }}" class="mt-1">
            @csrf
            <div class="flex-row flex-start">
                <div>
                    <label>Статус</label><br>
                    @if($isIndividual)
                    <select name="completion_status" required id="adm-status-{{ $lesson->id }}"
                            onchange="admToggle({{ $lesson->id }}, this.value, true)">
                        <option value="full" selected>Повне заняття</option>
                        <option value="partial">Часткове</option>
                        <option value="cancelled">Скасовано</option>
                    </select>
                    @else
                    <select name="completion_status" required id="adm-status-{{ $lesson->id }}"
                            onchange="admToggle({{ $lesson->id }}, this.value, false)">
                        <option value="full" selected>Повне заняття</option>
                        <option value="cancelled">Скасовано</option>
                        <option value="rescheduled">Перенесено</option>
                    </select>
                    @endif
                </div>
                @if($isIndividual)
                <div id="adm-minutes-{{ $lesson->id }}" style="display:none;">
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
            <div id="adm-makeup-{{ $lesson->id }}" class="makeup-panel" style="display:none;">
                <label>
                    <input type="checkbox" name="schedule_makeup" value="1"
                           id="adm-makeup-cb-{{ $lesson->id }}"
                           onchange="admMakeupDate({{ $lesson->id }})">
                    Запланувати відпрацювання
                </label>
                <div id="adm-makeup-date-{{ $lesson->id }}" class="makeup-date-row" style="display:none;">
                    <input type="date" name="makeup_date">
                    <input type="time" name="makeup_start">
                    <input type="time" name="makeup_end">
                </div>
            </div>
            <button type="submit" class="btn mt-1">Зберегти звіт</button>
        </form>
    </div>
    @endforeach
</div>
@push('scripts')
<script>
function admToggle(id, status, isInd) {
    const m = document.getElementById('adm-minutes-' + id);
    const mk = document.getElementById('adm-makeup-' + id);
    if (m)  m.style.display  = (status === 'partial') ? 'block' : 'none';
    if (mk) mk.style.display = (status === 'cancelled' || status === 'rescheduled') ? 'block' : 'none';
}
function admMakeupDate(id) {
    const cb = document.getElementById('adm-makeup-cb-' + id);
    document.getElementById('adm-makeup-date-' + id).style.display = cb.checked ? 'flex' : 'none';
}
</script>
@endpush
@endif

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

@if(auth()->user()->isSuperAdmin())
<h2 class="section-header">
    Останні транзакції
    <a href="{{ route('superadmin.transactions') }}" class="text-sm">Всі →</a>
</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Дата</th>
            <th>Користувач</th>
            <th>Тип</th>
            <th>Опис</th>
            <th style="text-align:right;">Сума</th>
        </tr>
    </thead>
    <tbody>
    @foreach($recentTransactions as $tx)
    <tr>
        <td class="text-muted">{{ $tx->created_at->format('d.m.y H:i') }}</td>
        <td>{{ $tx->user->full_name }}</td>
        <td class="text-muted">{{ $tx->type }}</td>
        <td>{{ $tx->description }}</td>
        <td style="text-align:right;font-weight:600;color:{{ $tx->amount >= 0 ? '#27ae60' : '#e74c3c' }};">{{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection