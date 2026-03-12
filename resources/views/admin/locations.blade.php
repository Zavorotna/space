@extends('layouts.app')
@section('title', 'Локації та аудиторії')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Локації та аудиторії</h1>

{{-- Add location --}}
<h2>Нова локація</h2>
<form method="POST" action="{{ route('admin.locations.store') }}">
    @csrf
    <div>
        <label>Назва</label>
        <input type="text" name="name" required>
    </div>
    <div>
        <label>Адреса</label>
        <input type="text" name="address">
    </div>
    <div>
        <label>Початок робочих годин</label>
        <input type="time" name="work_start" value="09:00" required>
    </div>
    <div>
        <label>Кінець робочих годин</label>
        <input type="time" name="work_end" value="21:00" required>
    </div>
    <button type="submit">Створити</button>
</form>

<hr>

{{-- Existing locations --}}
@foreach($locations as $location)
<div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
    <h3>{{ $location->name }}</h3>
    <p>{{ $location->address }}</p>
    <p>Години роботи: {{ $location->work_start }} — {{ $location->work_end }}</p>

    <h4>Аудиторії</h4>
    @if($location->classrooms->count())
        <ul>
        @foreach($location->classrooms as $room)
            <li>{{ $room->name }} (місткість: {{ $room->capacity ?? '—' }})</li>
        @endforeach
        </ul>
    @else
        <p>Ще немає аудиторій.</p>
    @endif

    {{-- Add classroom --}}
    <form method="POST" action="{{ route('admin.classrooms.store', $location) }}">
        @csrf
        <input type="text" name="name" placeholder="Назва аудиторії" required>
        <input type="number" name="capacity" placeholder="Місткість" min="1">
        <button type="submit">Додати аудиторію</button>
    </form>
</div>
@endforeach

@if($locations->isEmpty())
    <p>Ще немає локацій.</p>
@endif
@endsection
