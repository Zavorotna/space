@extends('layouts.app')
@section('title', 'Гаманець')
@section('content')
<h1>Гаманець</h1>
<p>Баланс: <strong>{{ $wallet->balance }}</strong> монет</p>

<div>
    <a href="{{ route('wallet.transfer') }}">Переказати</a>
    <a href="{{ route('wallet.topup') }}">Поповнити</a>
    <a href="{{ route('wallet.withdraw') }}">Вивести</a>
</div>

<div>
    <a href="{{ route('bonuses.index') }}">Мої бонуси</a>
    <form method="POST" action="{{ route('wallet.vip') }}" class="form-inline">
        @csrf
        <button type="submit" onclick="return confirm('VIP статус на 3 місяці за 500 монет?')">
            @if(auth()->user()->isVip()) ⭐ VIP (до {{ auth()->user()->vip_expires_at->format('d.m.Y') }})
            @else Купити VIP (500 монет) @endif
        </button>
    </form>
</div>

<h2>Транзакції</h2>
@if($transactions->isEmpty())
    <p>Транзакцій ще немає.</p>
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
{{ $transactions->links() }}
@endif
@endsection
