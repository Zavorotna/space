@extends('layouts.app')
@section('title', 'Дашборд студента')
@section('content')
<h1>Дашборд</h1>

@include('partials._admin_banners')

@if($currentCourse)
<div style="margin-bottom:20px;">
    <h2>{{ $currentCourse->title }}</h2>
    <p>{{ $currentCourse->description }}</p>
    <p>Успішність: {{ $currentCourse->pivot->success_rate }}%</p>
    <progress value="{{ $currentCourse->pivot->success_rate }}" max="100"></progress>
    <p>Викладач: {{ $currentCourse->teacher->full_name }}</p>
    <a href="{{ route('courses.student.show', $currentCourse) }}">Детальніше</a>
</div>
@else
<p>Ви не записані на жодний активний курс. <a href="{{ route('courses.public') }}">Переглянути курси</a></p>
@endif

{{-- ── Calendar (read-only) ── --}}
@include('partials._calendar', [
    'schedDate'    => $schedDate,
    'schedMode'    => $schedMode,
    'schedLessons' => $schedLessons,
    'schedEvents'  => $schedEvents,
    'canEdit'      => false,
])

<h2>Домашні завдання</h2>
<p>Здати: {{ $totalHomeworkToDo }} | На доопрацювання: {{ $pendingHomework }}</p>

<h2>Замітки</h2>
@foreach($receivedNotes as $note)
<div>
    <strong>{{ $note->author->full_name }}:</strong> {{ $note->content }}
    <form method="POST" action="{{ route('notes.read', $note) }}" style="display:inline">
        @csrf
        <button type="submit">Прочитано</button>
    </form>
</div>
@endforeach

@foreach($notes as $note)
<div>{{ $note->content }}</div>
@endforeach

<form method="POST" action="{{ route('notes.store') }}">
    @csrf
    <textarea name="content" placeholder="Нова замітка..." required></textarea>
    <button type="submit">Зберегти</button>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong>{{ $wallet->balance }}</strong> монет</p>
<a href="{{ route('wallet.transfer') }}">Переказати</a>
<a href="{{ route('wallet.topup') }}">Поповнити</a>
<a href="{{ route('wallet.withdraw') }}">Вивести</a>

<h3>Транзакції</h3>
@if($transactions->isEmpty())
<p style="color:#aaa;">Немає транзакцій.</p>
@else
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
@endif
@endsection
