@extends('layouts.app')
@section('title', 'Виведення')
@section('content')
<h1>Виведення хештегів</h1>
<p>Баланс: <strong>{{ $wallet->balance }}</strong></p>

<form method="POST" action="{{ route('wallet.withdraw.process') }}">
    @csrf
    <div><label>Сума</label><input type="number" name="amount" min="100" step="100" placeholder="Введіть суму" required></div>
    <div>
        @foreach([100,200,300,400,500] as $amt)
            <button type="button" onclick="document.querySelector('[name=amount]').value={{ $amt }}">{{ $amt }}</button>
        @endforeach
    </div>
    <p>Мінімум 100 монет, кратно 100. Комісія: 0%.</p>
    <p>Після підтвердження адміністратор вкаже де забрати готівку.</p>
    <button type="submit">Підтвердити</button>
</form>

<h3>Транзакції</h3>
<table>
    <thead><tr><th>Дата</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    @foreach($transactions as $tx)
    <tr><td>{{ $tx->created_at->format('d.m.y') }}</td><td>{{ $tx->description }}</td><td>{{ $tx->amount }}</td></tr>
    @endforeach
    </tbody>
</table>
@endsection
