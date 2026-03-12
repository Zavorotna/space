@extends('layouts.app')
@section('title', 'Додати товар')

@section('content')
<a href="{{ route('admin.shop.index') }}">&larr; Товари</a>

<h1>Додати товар</h1>

<form method="POST" action="{{ route('admin.shop.store') }}" enctype="multipart/form-data">
    @csrf

    <div>
        <label>Назва</label>
        <input type="text" name="title" value="{{ old('title') }}" required>
    </div>

    <div>
        <label>Опис</label>
        <textarea name="description" rows="4">{{ old('description') }}</textarea>
    </div>

    <div>
        <label>Ціна (монети)</label>
        <input type="number" name="price_coins" value="{{ old('price_coins', 0) }}" min="0" required>
    </div>

    <div>
        <label>Ціна (грн)</label>
        <input type="number" name="price_uah" value="{{ old('price_uah', 0) }}" min="0" step="0.01" required>
    </div>

    <div>
        <label>Кількість на складі</label>
        <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required>
    </div>

    <div>
        <label>Фото (можна декілька)</label>
        <input type="file" name="photos[]" multiple accept="image/*">
    </div>

    <button type="submit">Додати</button>
</form>
@endsection
