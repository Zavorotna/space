@extends('layouts.app')
@section('title', 'Оплата')
@section('content')
<h2>Перенаправлення на LiqPay...</h2>
<form id="liqpay-form" method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
    <input type="hidden" name="data" value="{{ $paymentData['data'] }}">
    <input type="hidden" name="signature" value="{{ $paymentData['signature'] }}">
    <button type="submit">Перейти до оплати</button>
</form>
<script>document.getElementById('liqpay-form').submit();</script>
@endsection
