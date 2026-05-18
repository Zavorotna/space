@extends('layouts.app')
@section('title', 'Адмін панель')
@section('content')
<h1>Адмін панель</h1>

@include('partials._admin_banners')

@include('partials._lessons_needing_report', ['lessonsNeedingReport' => $lessonsNeedingReport])

@if($pendingApplications > 0)
<p class="mb-1">
    Заявок на розгляді: <strong>{{ $pendingApplications }}</strong>
    — <a href="{{ route('teacher.courses.index') }}">Переглянути шаблони</a>
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

@if(auth()->user()->isSuperAdmin())
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

<button type="button" class="btn btn-ghost mt-1" onclick="this.style.display='none';document.getElementById('note-form-admin').style.display='block'">+ Додати замітку</button>
<form id="note-form-admin" method="POST" action="{{ route('notes.store') }}" style="display:none;">
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
        <button type="button" class="btn btn-ghost" onclick="this.closest('form').style.display='none';document.querySelector('[onclick*=note-form-admin]').style.display=''">Скасувати</button>
    </div>
</form>

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