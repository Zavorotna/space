@extends('layouts.app')
@section('title', 'Оплата курсу: ' . $course->title)

@section('content')
<a href="{{ route('courses.student.show', $course) }}">&larr; Назад</a>

<h1>Оплата курсу</h1>

<div>
    <h2>{{ $course->title }}</h2>
    <p>Ціна: {{ $course->price }} грн/міс</p>

    @if($discount > 0)
        <p>Знижка (сертифікат): -{{ $discount }}%</p>
    @endif

    @if(auth()->user()->isVip())
        <p>VIP знижка: -5%</p>
    @endif

    <p><strong>До сплати: {{ $finalPrice }} грн</strong></p>
</div>

<h3>Оплата карткою (LiqPay)</h3>
<form method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
    <input type="hidden" name="data" value="{{ $paymentData['data'] ?? '' }}">
    <input type="hidden" name="signature" value="{{ $paymentData['signature'] ?? '' }}">
    <button type="submit">Оплатити карткою — {{ $finalPrice }} грн</button>
</form>

<hr>

<h3>Або оплата монетами</h3>
@php $wallet = auth()->user()->getOrCreateWallet(); @endphp
<p>Ваш баланс: {{ $wallet->balance }} монет</p>
@if($wallet->balance >= $finalPrice)
    <form method="POST" action="{{ route('courses.pay.process', $course) }}">
        @csrf
        <button type="submit">Оплатити монетами — {{ $finalPrice }} монет</button>
    </form>
@else
    <p>Недостатньо монет. <a href="{{ route('wallet.topup') }}">Поповнити</a></p>
@endif
@endsection
