@extends('layouts.app')
@section('title', 'Hashtag Space — IT Academy')
@section('content')
<h1>HASHTAG SPACE</h1>
<p>IT Academy Hashtag — онлайн та офлайн курси</p>
<a href="{{ route('register') }}">Зареєструватися</a>
<a href="{{ route('login') }}">Увійти</a>
@endsection
