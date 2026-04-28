@extends('layouts.app')
@section('title', 'Запити на виведення')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Запити на виведення монет</h1>

{{-- Filter --}}
<form method="GET" action="{{ route('superadmin.withdrawals') }}">
    <select name="status">
        <option value="">— Всі статуси —</option>
        <option value="pending" @selected(request('status') === 'pending')>Очікує</option>
        <option value="approved" @selected(request('status') === 'approved')>Підтверджено</option>
        <option value="rejected" @selected(request('status') === 'rejected')>Відхилено</option>
    </select>
    <button type="submit">Фільтрувати</button>
</form>

<hr>

@if($requests->isEmpty())
    <p>Немає запитів.</p>
@else
<table>
    <thead>
        <tr><th>Дата</th><th>Користувач</th><th>Сума</th><th>Статус</th><th>Дії</th></tr>
    </thead>
    <tbody>
    @foreach($requests as $req)
        <tr>
            <td>{{ $req->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $req->user->last_name ?? '' }} {{ $req->user->first_name ?? '' }}</td>
            <td>{{ $req->amount }}</td>
            <td>
                @switch($req->status)
                    @case('pending') 🟡 Очікує @break
                    @case('approved') ✅ Підтверджено @break
                    @case('rejected') ❌ Відхилено @break
                @endswitch
            </td>
            <td>
                @if($req->status === 'pending')
                    <form method="POST" action="{{ route('superadmin.withdrawals.approve', $req) }}" class="form-inline">
                        @csrf
                        <input type="text" name="pickup_note" placeholder="Куди підійти для отримання" required>
                        <button type="submit">Підтвердити</button>
                    </form>
                    <form method="POST" action="{{ route('superadmin.withdrawals.reject', $req) }}" class="form-inline"
                          onsubmit="return confirm('Відхилити? Монети повернуться користувачу.')">
                        @csrf
                        <button type="submit">Відхилити</button>
                    </form>
                @endif
                @if($req->pickup_note)
                    <p>Примітка: {{ $req->pickup_note }}</p>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $requests->links() }}
@endif
@endsection
