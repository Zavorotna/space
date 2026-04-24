@extends('layouts.app')
@section('title', 'Локації та аудиторії')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Локації та аудиторії</h1>

<datalist id="cities-list">
    @foreach($cities as $city)
        <option value="{{ $city }}">
    @endforeach
</datalist>

{{-- Add location --}}
<h2>Нова локація</h2>
<form method="POST" action="{{ route('admin.locations.store') }}">
    @csrf
    <div><label>Назва</label><input type="text" name="name" required></div>
    <div>
        <label>Місто</label>
        <input type="text" name="city" id="new-city" list="cities-list"
               placeholder="Почніть вводити місто" autocomplete="off"
               onblur="addCityToList(this.value)">
    </div>
    <div><label>Вулиця / адреса</label><input type="text" name="street" placeholder="вул. Проскурівська 42"></div>
    <div>
        <label>Години роботи</label>
        <input type="time" name="work_start" value="09:00" required>
        —
        <input type="time" name="work_end" value="21:00" required>
    </div>
    <button type="submit">Створити</button>
</form>

<hr>

{{-- Existing locations --}}
@forelse($locations as $location)
<div style="border:1px solid #ccc; padding:10px; margin:10px 0;">

    <form method="POST" action="{{ route('admin.locations.update', $location) }}">
        @csrf @method('PUT')
        <div><label>Назва</label><input type="text" name="name" value="{{ $location->name }}" required></div>
        <div>
            <label>Місто</label>
            <input type="text" name="city" list="cities-list"
                   value="{{ $location->city }}" autocomplete="off"
                   onblur="addCityToList(this.value)">
        </div>
        <div><label>Вулиця / адреса</label><input type="text" name="street" value="{{ $location->street }}"></div>
        <div>
            <label>Години роботи</label>
            <input type="time" name="work_start" value="{{ substr($location->work_start, 0, 5) }}" required>
            —
            <input type="time" name="work_end" value="{{ substr($location->work_end, 0, 5) }}" required>
        </div>
        <button type="submit">Зберегти</button>
    </form>

    <form method="POST" action="{{ route('admin.locations.destroy', $location) }}" style="display:inline; margin-top:6px;">
        @csrf @method('DELETE')
        <button type="submit" onclick="return confirm('Видалити локацію «{{ $location->name }}»?')">Видалити локацію</button>
    </form>

    <h4>Аудиторії</h4>
    @foreach($location->classrooms as $room)
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:4px;">
        <form method="POST" action="{{ route('admin.classrooms.update', $room) }}" style="display:inline-flex; gap:6px;">
            @csrf @method('PUT')
            <input type="text" name="name" value="{{ $room->name }}" required size="15">
            <input type="number" name="capacity" value="{{ $room->capacity }}" min="1" size="5">
            <button type="submit">Зберегти</button>
        </form>
        <form method="POST" action="{{ route('admin.classrooms.destroy', $room) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('Видалити аудиторію «{{ $room->name }}»?')">Видалити</button>
        </form>
    </div>
    @endforeach

    @if($location->classrooms->isEmpty())
        <p>Ще немає аудиторій.</p>
    @endif

    <form method="POST" action="{{ route('admin.classrooms.store', $location) }}" style="margin-top:8px;">
        @csrf
        <input type="text" name="name" placeholder="Назва аудиторії" required>
        <input type="number" name="capacity" placeholder="Місткість" min="1">
        <button type="submit">Додати аудиторію</button>
    </form>

</div>
@empty
    <p>Ще немає локацій.</p>
@endforelse

<script>
const datalist = document.getElementById('cities-list');

function addCityToList(value) {
    value = value.trim();
    if (!value) return;

    const options = Array.from(datalist.options).map(o => o.value.toLowerCase());
    if (!options.includes(value.toLowerCase())) {
        const option = document.createElement('option');
        option.value = value;
        datalist.appendChild(option);
    }
}
</script>
@endsection