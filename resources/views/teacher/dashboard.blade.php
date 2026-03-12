@extends('layouts.app')
@section('title', 'Дашборд викладача')
@section('content')
<h1>Розклад занять</h1>

<table>
    <thead><tr><th>День</th><th>10:00</th><th>12:00</th><th>14:00</th><th>16:00</th><th>18:00</th></tr></thead>
    <tbody>
    @php
        $weekDays = $weekSchedule->groupBy(fn($l) => $l->date->format('D d.m'));
    @endphp
    @foreach($weekDays as $day => $lessons)
    <tr>
        <td>{{ $day }}</td>
        @foreach(['10:00','12:00','14:00','16:00','18:00'] as $time)
        <td>
            @php $match = $lessons->first(fn($l) => $l->start_time <= $time && $l->end_time > $time); @endphp
            {{ $match ? $match->course->title : '' }}
        </td>
        @endforeach
    </tr>
    @endforeach
    </tbody>
</table>

<a href="{{ route('schedule.index') }}">Переглянути повний розклад</a>

<h2>Запити на заняття</h2>
@foreach($courses as $course)
    @if($course->applications()->where('status','pending')->count() > 0)
        <a href="{{ route('teacher.courses.applications', $course) }}">
            {{ $course->title }}: {{ $course->applications()->where('status','pending')->count() }} заявок
        </a>
    @endif
@endforeach

<h2>Прогрес курсів (групи)</h2>
@foreach($courses as $course)
<div>
    <strong>{{ $course->title }}</strong>
    @php
        $progress = 0;
        if ($course->start_date && $course->end_date) {
            $total = $course->start_date->diffInDays($course->end_date);
            $elapsed = $course->start_date->diffInDays(now());
            $progress = $total > 0 ? min(100, round($elapsed / $total * 100)) : 0;
        }
    @endphp
    {{ $progress }}%
    <progress value="{{ $progress }}" max="100"></progress>
    <span>{{ $course->start_date?->format('d.m') }} — {{ $course->end_date?->format('d.m') }}</span>
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
