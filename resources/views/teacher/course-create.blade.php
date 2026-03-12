@extends('layouts.app')
@section('title', 'Створення курсу')
@section('content')
<h1>Створення курсу</h1>
<form method="POST" action="{{ route('teacher.courses.store') }}" enctype="multipart/form-data">
    @csrf
    <div><label>Фото курсу</label><input type="file" name="cover" accept="image/*"></div>
    <div><label>Назва курсу</label><input type="text" name="title" value="{{ old('title') }}" required></div>
    <div><label>Опис курсу</label><textarea name="description">{{ old('description') }}</textarea></div>
    <div><label>Програма</label><textarea name="program">{{ old('program') }}</textarea></div>
    <div><label>Тип</label>
        <select name="type"><option value="group">Груповий</option><option value="individual">Індивідуальний</option></select>
    </div>
    <div><label>Ціна (грн)</label><input type="number" name="price" step="0.01" value="{{ old('price', 0) }}"></div>
    <div><label>Період оплати</label>
        <select name="billing_period"><option value="monthly">Щомісячно</option><option value="one_time">Разово</option></select>
    </div>
    <div><label>Telegram посилання</label><input type="url" name="telegram_link" value="{{ old('telegram_link') }}"></div>
    <div><label>Дата відкритого заняття</label><input type="date" name="intro_date" value="{{ old('intro_date') }}"></div>
    <div><label>Дата початку</label><input type="date" name="start_date" value="{{ old('start_date') }}"></div>
    <div><label>Дата закінчення</label><input type="date" name="end_date" value="{{ old('end_date') }}"></div>
    <div><label><input type="checkbox" name="has_graduation_project" value="1" checked> Є випускний проєкт</label></div>
    <button type="submit">Зберегти</button>
</form>
@endsection
