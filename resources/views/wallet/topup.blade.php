@extends('layouts.app')
@section('title', 'Поповнення балансу')
@section('content')
<h1>Поповнення балансу</h1>
<p>Баланс: <strong>{{ $wallet->balance }}</strong></p>

<h2>Оберіть спосіб поповнення</h2>
<form method="POST" action="{{ route('wallet.topup.process') }}">
    @csrf
    <div>
        <label><input type="radio" name="method" value="card" checked> З банківської картки</label>
    </div>
    <div><label>Сума</label><input type="number" name="amount" min="1" placeholder="Введіть суму" required></div>
    <div>
        @foreach([100,200,300,400,500] as $amt)
            <button type="button" onclick="document.querySelector('[name=amount]').value={{ $amt }}">{{ $amt }}</button>
        @endforeach
    </div>
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
