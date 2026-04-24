@extends('layouts.app')
@section('title', 'Управління магазином')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Управління товарами</h1>

<a href="{{ route('admin.shop.create') }}">+ Додати товар</a>

<hr>

<table>
    <thead>
        <tr><th>ID</th><th>Назва</th><th>HC</th><th>Склад</th><th>Активний</th><th>Дії</th></tr>
    </thead>
    <tbody>
    @foreach($products as $product)
        <tr>
            <td>{{ $product->id }}</td>
            <td>{{ $product->title }}</td>
            <td>{{ $product->price_coins }}</td>
            <td>{{ $product->stock }}</td>
            <td>{{ $product->is_active ? '✅' : '❌' }}</td>
            <td>
                <form method="POST" action="{{ route('admin.shop.update', $product) }}">
                    @csrf @method('PUT')
                    <input type="text" name="title" value="{{ $product->title }}" size="15">
                    <input type="number" name="price_coins" value="{{ $product->price_coins }}" size="5">
                    <input type="number" name="stock" value="{{ $product->stock }}" size="5">
                    <input type="hidden" name="description" value="{{ $product->description }}">
                    <label>
                        <input type="checkbox" name="is_active" value="1" @checked($product->is_active)> Активний
                    </label>
                    <button type="submit">Зберегти</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $products->links() }}
@endsection
