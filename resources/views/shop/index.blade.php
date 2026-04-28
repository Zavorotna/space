@extends('layouts.app')
@section('title', 'Магазин')

@section('content')
<h1>Магазин</h1>

@if(auth()->user()->isAdmin())
    <div>
        <a href="{{ route('admin.shop.create') }}">+ Додати товар</a>
        <a href="{{ route('admin.shop.index') }}">Управління товарами</a>
    </div>
@endif

@if($products->isEmpty())
    <p>Наразі товарів немає.</p>
@else
    <div class="product-grid">
    @foreach($products as $product)
        <div class="product-card">
            @if($product->getFirstMediaUrl('photos'))
                <img src="{{ $product->getFirstMediaUrl('photos') }}" alt="{{ $product->title }}">
            @endif
            <h3>{{ $product->title }}</h3>
            <p>{{ Str::limit($product->description, 80) }}</p>
            <p>
                <strong title="1 Hashtag Coin = 1 грн">{{ $product->price_coins }} HC</strong>
            </p>
            <p>На складі: {{ $product->stock }}</p>
            <a href="{{ route('shop.show', $product) }}">Детальніше</a>
        </div>
    @endforeach
    </div>

    {{ $products->links() }}
@endif
@endsection