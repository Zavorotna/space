@extends('layouts.app')
@section('title', 'Адмін панель')
@section('content')
<h1>Адмін панель</h1>

@include('partials._admin_banners')

@if($pendingApplications > 0)
<p style="margin-bottom:12px;">
    Заявок на розгляді: <strong>{{ $pendingApplications }}</strong>
</p>
@endif

@if(auth()->user()->isSuperAdmin() && $pendingWithdrawalsList->count() > 0)
<div style="border:2px solid #e67e22;padding:14px;margin-bottom:20px;border-radius:8px;">
    <h2 style="color:#e67e22;margin:0 0 10px;">Запити на виведення ({{ $pendingWithdrawalsList->count() }})</h2>
    @foreach($pendingWithdrawalsList as $req)
    <div style="border:1px solid #eee;padding:10px;margin:6px 0;border-radius:5px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start;">
        <div>
            <strong>{{ $req->user->last_name }} {{ $req->user->first_name }}</strong><br>
            <span style="font-size:.85rem;color:#888;">{{ $req->amount }} монет · {{ $req->created_at->format('d.m.Y H:i') }}</span>
        </div>
        <form method="POST" action="{{ route('superadmin.withdrawals.approve', $req) }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
            @csrf
            <input type="text" name="pickup_note" placeholder="Куди підійти" required style="width:180px;">
            <button type="submit" style="background:#27ae60;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">Видати готівку</button>
        </form>
        <form method="POST" action="{{ route('superadmin.withdrawals.reject', $req) }}"
              onsubmit="return confirm('Відхилити? Монети повернуться.')">
            @csrf
            <button type="submit" style="background:#e74c3c;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">Відхилити</button>
        </form>
    </div>
    @endforeach
</div>
@endif

{{-- ── Lesson completion reports ── --}}
@if($lessonsNeedingReport->count())
<div style="border:2px solid #e67e22;padding:15px;margin-bottom:20px;border-radius:8px;">
    <h2 style="color:#e67e22;margin-top:0;">Потрібен звіт ({{ $lessonsNeedingReport->count() }})</h2>
    @foreach($lessonsNeedingReport as $lesson)
    @php $isIndividual = $lesson->course->type === 'individual'; @endphp
    <div style="border:1px solid #ddd;padding:10px;margin:8px 0;border-radius:4px;">
        <strong>{{ $lesson->date->format('d.m.Y') }}</strong>
        · {{ $lesson->course->title }}
        {{ $lesson->title ? "· {$lesson->title}" : '' }}
        · {{ substr($lesson->start_time,0,5) }}–{{ substr($lesson->end_time,0,5) }}
        ({{ $lesson->plannedMinutes() }} хв)
        <span style="color:#888;font-size:.85em;">{{ $isIndividual ? 'Індивідуальне' : 'Групове' }}</span>

        <form method="POST" action="{{ route('teacher.schedule.complete', $lesson) }}" style="margin-top:8px;">
            @csrf
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;">
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
                           placeholder="{{ round($lesson->plannedMinutes() / 60, 1) }}" style="width:70px;">
                </div>
                @endif
                <div>
                    <label>Примітка</label><br>
                    <input type="text" name="completion_note" placeholder="необов'язково" style="width:200px;">
                </div>
            </div>
            <div id="adm-makeup-{{ $lesson->id }}" style="display:none;margin-top:8px;padding:8px;background:#fff8e1;border-radius:4px;">
                <label>
                    <input type="checkbox" name="schedule_makeup" value="1"
                           id="adm-makeup-cb-{{ $lesson->id }}"
                           onchange="admMakeupDate({{ $lesson->id }})">
                    Запланувати відпрацювання
                </label>
                <div id="adm-makeup-date-{{ $lesson->id }}" style="display:none;margin-top:6px;">
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
    document.getElementById('adm-makeup-date-' + id).style.display = cb.checked ? 'block' : 'none';
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
<h2 style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    Останні транзакції
    <a href="{{ route('superadmin.transactions') }}" style="font-size:.82rem;font-weight:normal;">Всі →</a>
</h2>
<table style="width:100%;border-collapse:collapse;font-size:.88rem;">
    <thead>
        <tr style="background:#f7f8fa;">
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Дата</th>
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Користувач</th>
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Тип</th>
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Опис</th>
            <th style="padding:6px 8px;text-align:right;border-bottom:1px solid #eee;">Сума</th>
        </tr>
    </thead>
    <tbody>
    @foreach($recentTransactions as $tx)
    <tr>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;color:#888;">{{ $tx->created_at->format('d.m.y H:i') }}</td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;">{{ $tx->user->full_name }}</td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;color:#888;">{{ $tx->type }}</td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;">{{ $tx->description }}</td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;text-align:right;font-weight:600;color:{{ $tx->amount >= 0 ? '#27ae60' : '#e74c3c' }};">{{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection
