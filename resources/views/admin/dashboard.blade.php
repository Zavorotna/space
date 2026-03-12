@extends('layouts.app')
@section('title', 'Адмін панель')
@section('content')
<h1>Адмін панель</h1>

<div>
    <p>Студентів: {{ $totalStudents }}</p>
    <p>Активних курсів: {{ $activeCourses }}</p>
    <p>Заявок на розгляді: {{ $pendingApplications }}</p>
    @if(auth()->user()->isSuperAdmin())
        <p>Запитів на виведення: <a href="{{ route('superadmin.withdrawals') }}">{{ $pendingWithdrawals }}</a></p>
    @endif
</div>

<h2>Заняття сьогодні</h2>
@foreach($todayLessons as $lesson)
<div>
    {{ $lesson->start_time }} - {{ $lesson->end_time }}
    | {{ $lesson->course->title }}
    | {{ $lesson->teacher->full_name }}
</div>
@endforeach

<h2>Навігація</h2>
<ul>
    <li><a href="{{ route('admin.users') }}">Користувачі</a></li>
    <li><a href="{{ route('admin.locations') }}">Локації та аудиторії</a></li>
    <li><a href="{{ route('admin.shop.index') }}">Магазин</a></li>
    @if(auth()->user()->isSuperAdmin())
        <li><a href="{{ route('superadmin.transactions') }}">Всі транзакції</a></li>
        <li><a href="{{ route('superadmin.withdrawals') }}">Виведення коштів</a></li>
    @endif
</ul>

@if(auth()->user()->isSuperAdmin())
<h2>Останні транзакції</h2>
<table>
    <thead><tr><th>Дата</th><th>Користувач</th><th>Тип</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    @foreach($recentTransactions as $tx)
    <tr>
        <td>{{ $tx->created_at->format('d.m.y H:i') }}</td>
        <td>{{ $tx->user->full_name }}</td>
        <td>{{ $tx->type }}</td>
        <td>{{ $tx->description }}</td>
        <td>{{ $tx->amount }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection
