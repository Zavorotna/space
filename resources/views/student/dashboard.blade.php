@extends('layouts.app')
@section('title', 'Дашборд студента')
@section('content')
<h1>Дашборд</h1>

@include('partials._admin_banners')

@if($currentCourse)
<div class="mb-3">
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
    'schedNotes'   => $schedNotes,
    'canEdit'      => false,
])

<h2>Домашні завдання</h2>
<p>Здати: {{ $totalHomeworkToDo }} | На доопрацювання: {{ $pendingHomework }}</p>

<h2>Повідомлення від викладачів</h2>
@forelse($receivedNotes as $note)
<div style="padding:10px; background:#f5f5f5; margin:5px 0; border-radius:4px;">
    <strong>{{ $note->author->full_name }}:</strong> {{ $note->content }}
    <form method="POST" action="{{ route('notes.read', $note) }}" class="form-inline">
        @csrf
        <button type="submit" class="btn btn-xs btn-ghost">✓ Прочитано</button>
    </form>
</div>
@empty
<p class="text-subtle">Немає повідомлень.</p>
@endforelse

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

<button type="button" class="btn btn-ghost mt-1" onclick="this.style.display='none';document.getElementById('note-form-student').style.display='block'">+ Додати замітку</button>
<form id="note-form-student" method="POST" action="{{ route('notes.store') }}" style="display:none;">
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
        <button type="button" class="btn btn-ghost" onclick="this.closest('form').style.display='none';document.querySelector('[onclick*=note-form-student]').style.display=''">Скасувати</button>
    </div>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong>{{ $wallet->balance }}</strong> монет</p>
<a href="{{ route('wallet.transfer') }}">Переказати</a>
<a href="{{ route('wallet.topup') }}">Поповнити</a>
<a href="{{ route('wallet.withdraw') }}">Вивести</a>

<h3>Транзакції</h3>
@if($transactions->isEmpty())
<p class="text-subtle">Немає транзакцій.</p>
@else
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
@endif
@endsection