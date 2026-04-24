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
                 class="product-photo {{ $i === 0 ? 'active' : '' }}">
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

<p><strong>Ціна:</strong> <span title="1 Hashtag Coin = 1 грн">{{ $product->price_coins }} HC</span></p>
<p><strong>На складі:</strong> {{ $product->stock }}</p>

@auth
@if($product->stock > 0)
<hr>
<h3>Купити</h3>
@php $balance = auth()->user()->getOrCreateWallet()->balance; @endphp
<form method="POST" action="{{ route('shop.purchase', $product) }}" id="buy-form">
    @csrf
    <input type="hidden" name="payment_method" value="coins">
    <div>
        <label>Кількість</label>
        <input type="number" name="quantity" id="qty" value="1" min="1" max="{{ $product->stock }}"
               oninput="checkBalance()">
    </div>
    <p>Ваш баланс: <strong>{{ $balance }} HC</strong></p>
    <div id="balance-warning" style="display:none;">
        <p>Недостатньо монет.
            <a href="{{ route('wallet.topup') }}">Поповнити баланс</a>
        </p>
    </div>
    <button type="submit" id="buy-btn">Купити</button>
</form>
<script>
const price = {{ $product->price_coins }};
const balance = {{ $balance }};
function checkBalance() {
    const qty = parseInt(document.getElementById('qty').value) || 1;
    const enough = balance >= price * qty;
    document.getElementById('balance-warning').style.display = enough ? 'none' : 'block';
    document.getElementById('buy-btn').disabled = !enough;
}
checkBalance();
</script>
@else
    <p>Товар закінчився.</p>
@endif
@endauth

<script>
let currentPhoto = 0;
const photos = document.querySelectorAll('.product-photo');
const counter = document.getElementById('photo-counter');

function showPhoto(index) {
    photos.forEach(p => p.classList.remove('active'));
    photos[index].classList.add('active');
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