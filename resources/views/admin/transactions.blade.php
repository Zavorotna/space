@extends('layouts.app')
@section('title', 'Всі транзакції')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Всі транзакції</h1>

{{-- Filters --}}
<form method="GET" action="{{ route('superadmin.transactions') }}">
    <select name="type">
        <option value="">— Всі типи —</option>
        @foreach(['reward','penalty','deposit','withdrawal','transfer','purchase','course_payment','resume_purchase','donation','bonus_purchase','bonus_sell'] as $t)
            <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
        @endforeach
    </select>
    <input type="number" name="user_id" placeholder="ID користувача" value="{{ request('user_id') }}">
    <button type="submit">Фільтрувати</button>
</form>

<hr>

<table>
    <thead>
        <tr>
            <th>Дата</th><th>Користувач</th><th>Тип</th><th>Сума</th><th>Призначення</th><th>Кому</th>
        </tr>
    </thead>
    <tbody>
    @foreach($transactions as $tx)
        <tr>
            <td>{{ $tx->created_at->format('d.m.Y H:i') }}</td>
            <td>{{ $tx->user->last_name ?? '' }} {{ $tx->user->first_name ?? '' }} (#{{ $tx->user_id }})</td>
            <td>{{ $tx->type }}</td>
            <td style="color:{{ $tx->amount > 0 ? 'green' : 'red' }}">{{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}</td>
            <td>{{ $tx->description }}</td>
            <td>
                @if($tx->relatedUser)
                    {{ $tx->relatedUser->last_name ?? '' }} {{ $tx->relatedUser->first_name ?? '' }}
                @else
                    —
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $transactions->links() }}
@endsection
