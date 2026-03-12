@extends('layouts.app')
@section('title', $product->title)

@section('content')
<a href="{{ route('shop.index') }}">&larr; Магазин</a>

<h1>{{ $product->title }}</h1>

{{-- Photo slider --}}
@php $photos = $product->getMedia('photos'); @endphp
@if($photos->count())
    <div id="product-slider">
        @foreach($photos as $i => $photo)
            <img src="{{ $photo->getUrl() }}" alt="{{ $product->title }}"
                 style="max-width:400px; display:{{ $i === 0 ? 'block' : 'none' }};"
                 class="product-photo">
        @endforeach
        @if($photos->count() > 1)
            <button onclick="prevPhoto()">←</button>
            <span id="photo-counter">1 / {{ $photos->count() }}</span>
            <button onclick="nextPhoto()">→</button>
        @endif
    </div>
@endif

@if($product->description)
    <p>{!! nl2br(e($product->description)) !!}</p>
@endif

<p><strong>Ціна:</strong> {{ $product->price_coins }} монет / {{ $product->price_uah }} грн</p>
<p><strong>На складі:</strong> {{ $product->stock }}</p>

@auth
@if($product->stock > 0)
<hr>
<h3>Купити</h3>
<form method="POST" action="{{ route('shop.purchase', $product) }}">
    @csrf
    <div>
        <label>Кількість</label>
        <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}">
    </div>
    <div>
        <label>Спосіб оплати</label><br>
        <label><input type="radio" name="payment_method" value="coins" checked> Монети</label>
        <label><input type="radio" name="payment_method" value="card"> Картка (LiqPay)</label>
    </div>
    <button type="submit">Купити</button>
</form>
@else
    <p>Товар закінчився.</p>
@endif
@endauth

<script>
let currentPhoto = 0;
const photos = document.querySelectorAll('.product-photo');
const counter = document.getElementById('photo-counter');

function showPhoto(index) {
    photos.forEach(p => p.style.display = 'none');
    photos[index].style.display = 'block';
    if (counter) counter.textContent = (index + 1) + ' / ' + photos.length;
}

function nextPhoto() {
    currentPhoto = (currentPhoto + 1) % photos.length;
    showPhoto(currentPhoto);
}

function prevPhoto() {
    currentPhoto = (currentPhoto - 1 + photos.length) % photos.length;
    showPhoto(currentPhoto);
}
</script>
@endsection
