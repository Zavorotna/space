@extends('layouts.app')
@section('title', 'Магазин')

@section('content')
<h1>Магазин</h1>

@if($products->isEmpty())
    <p>Наразі товарів немає.</p>
@else
    <div>
    @foreach($products as $product)
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0; display:inline-block; vertical-align:top; width:250px;">
            @if($product->getFirstMediaUrl('photos'))
                <img src="{{ $product->getFirstMediaUrl('photos') }}" alt="{{ $product->title }}" style="max-width:230px; max-height:200px;">
            @endif
            <h3>{{ $product->title }}</h3>
            <p>{{ Str::limit($product->description, 80) }}</p>
            <p>
                <strong>{{ $product->price_coins }} монет</strong>
                / {{ $product->price_uah }} грн
            </p>
            <p>На складі: {{ $product->stock }}</p>
            <a href="{{ route('shop.show', $product) }}">Детальніше</a>
        </div>
    @endforeach
    </div>

    {{ $products->links() }}
@endif
@endsection
