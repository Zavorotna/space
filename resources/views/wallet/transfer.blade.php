@extends('layouts.app')
@section('title', 'Переказ')
@section('content')
<h1>Переказ</h1>
<p>Баланс: <strong>{{ $wallet->balance }}</strong></p>

<form method="POST" action="{{ route('wallet.transfer.process') }}">
    @csrf
    <div><label>Отримувач</label><input type="text" name="recipient" placeholder="Ім'я або номер телефону" required></div>
    <div><label>Сума</label><input type="number" name="amount" min="1" placeholder="Введіть суму" required></div>
    <div>
        @foreach([100,200,300,400,500] as $amt)
            <button type="button" onclick="document.querySelector('[name=amount]').value={{ $amt }}">{{ $amt }}</button>
        @endforeach
    </div>
    <div><label>Коментар</label><textarea name="comment" placeholder="Коментар"></textarea></div>
    <p><small>Комісія: 1%</small></p>
    <button type="submit">Підтвердити</button>
</form>

<h3>Транзакції</h3>
@if($transactions->isEmpty())
    <p>Транзакцій ще немає.</p>
@else
<table>
    <thead><tr><th>Дата</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    @foreach($transactions as $tx)
    <tr><td>{{ $tx->created_at->format('d.m.y') }}</td><td>{{ $tx->description }}</td><td>{{ $tx->amount }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif
@endsection
