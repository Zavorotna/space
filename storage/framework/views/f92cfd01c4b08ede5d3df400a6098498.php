<?php $__env->startSection('title', 'Магазин'); ?>

<?php $__env->startSection('content'); ?>
<h1>Магазин</h1>

<?php if(auth()->user()->isAdmin()): ?>
    <div>
        <a href="<?php echo e(route('admin.shop.create')); ?>">+ Додати товар</a>
        <a href="<?php echo e(route('admin.shop.index')); ?>">Управління товарами</a>
    </div>
<?php endif; ?>

<?php if($products->isEmpty()): ?>
    <p>Наразі товарів немає.</p>
<?php else: ?>
    <div>
    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0; display:inline-block; vertical-align:top; width:250px;">
            <?php if($product->getFirstMediaUrl('photos')): ?>
                <img src="<?php echo e($product->getFirstMediaUrl('photos')); ?>" alt="<?php echo e($product->title); ?>" style="max-width:230px; max-height:200px;">
            <?php endif; ?>
            <h3><?php echo e($product->title); ?></h3>
            <p><?php echo e(Str::limit($product->description, 80)); ?></p>
            <p>
                <strong title="1 Hashtag Coin = 1 грн"><?php echo e($product->price_coins); ?> HC</strong>
            </p>
            <p>На складі: <?php echo e($product->stock); ?></p>
            <a href="<?php echo e(route('shop.show', $product)); ?>">Детальніше</a>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php echo e($products->links()); ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/shop/index.blade.php ENDPATH**/ ?>