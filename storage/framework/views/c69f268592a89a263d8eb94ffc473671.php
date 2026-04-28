<?php $__env->startSection('title', 'Додати товар'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('admin.shop.index')); ?>">&larr; Товари</a>

<h1>Додати товар</h1>

<form method="POST" action="<?php echo e(route('admin.shop.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <div>
        <label>Назва</label>
        <input type="text" name="title" value="<?php echo e(old('title')); ?>" required>
    </div>

    <div>
        <label>Опис</label>
        <textarea name="description" rows="4"><?php echo e(old('description')); ?></textarea>
    </div>

    <div>
        <label>Ціна (Hashtag Coins, 1 HC = 1 грн)</label>
        <input type="number" name="price_coins" value="<?php echo e(old('price_coins', 0)); ?>" min="0" required>
    </div>

    <div>
        <label>Кількість на складі</label>
        <input type="number" name="stock" value="<?php echo e(old('stock', 0)); ?>" min="0" required>
    </div>

    <div>
        <label>Фото (можна декілька)</label>
        <input type="file" name="photos[]" multiple accept="image/*">
    </div>

    <button type="submit">Додати</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/shop-product-create.blade.php ENDPATH**/ ?>