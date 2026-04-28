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