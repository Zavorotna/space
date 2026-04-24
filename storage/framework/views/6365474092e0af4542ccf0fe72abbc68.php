<?php $__env->startSection('title', $product->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('shop.index')); ?>">&larr; Магазин</a>

<h1><?php echo e($product->title); ?></h1>


<?php $photos = $product->getMedia('photos'); ?>
<?php if($photos->count()): ?>
    <div id="product-slider">
        <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <img src="<?php echo e($photo->getUrl()); ?>" alt="<?php echo e($product->title); ?>"
                 class="product-photo <?php echo e($i === 0 ? 'active' : ''); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($photos->count() > 1): ?>
            <button onclick="prevPhoto()">←</button>
            <span id="photo-counter">1 / <?php echo e($photos->count()); ?></span>
            <button onclick="nextPhoto()">→</button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($product->description): ?>
    <p><?php echo nl2br(e($product->description)); ?></p>
<?php endif; ?>

<p><strong>Ціна:</strong> <span title="1 Hashtag Coin = 1 грн"><?php echo e($product->price_coins); ?> HC</span></p>
<p><strong>На складі:</strong> <?php echo e($product->stock); ?></p>

<?php if(auth()->guard()->check()): ?>
<?php if($product->stock > 0): ?>
<hr>
<h3>Купити</h3>
<?php $balance = auth()->user()->getOrCreateWallet()->balance; ?>
<form method="POST" action="<?php echo e(route('shop.purchase', $product)); ?>" id="buy-form">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="payment_method" value="coins">
    <div>
        <label>Кількість</label>
        <input type="number" name="quantity" id="qty" value="1" min="1" max="<?php echo e($product->stock); ?>"
               oninput="checkBalance()">
    </div>
    <p>Ваш баланс: <strong><?php echo e($balance); ?> HC</strong></p>
    <div id="balance-warning" style="display:none;">
        <p>Недостатньо монет.
            <a href="<?php echo e(route('wallet.topup')); ?>">Поповнити баланс</a>
        </p>
    </div>
    <button type="submit" id="buy-btn">Купити</button>
</form>
<script>
const price = <?php echo e($product->price_coins); ?>;
const balance = <?php echo e($balance); ?>;
function checkBalance() {
    const qty = parseInt(document.getElementById('qty').value) || 1;
    const enough = balance >= price * qty;
    document.getElementById('balance-warning').style.display = enough ? 'none' : 'block';
    document.getElementById('buy-btn').disabled = !enough;
}
checkBalance();
</script>
<?php else: ?>
    <p>Товар закінчився.</p>
<?php endif; ?>
<?php endif; ?>

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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/shop/show.blade.php ENDPATH**/ ?>