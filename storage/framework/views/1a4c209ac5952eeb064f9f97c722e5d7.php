<?php $__env->startSection('title', 'Управління магазином'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('dashboard')); ?>">&larr; Дашборд</a>

<h1>Управління товарами</h1>

<a href="<?php echo e(route('admin.shop.create')); ?>">+ Додати товар</a>

<hr>

<table>
    <thead>
        <tr><th>ID</th><th>Назва</th><th>HC</th><th>Склад</th><th>Активний</th><th>Дії</th></tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($product->id); ?></td>
            <td><?php echo e($product->title); ?></td>
            <td><?php echo e($product->price_coins); ?></td>
            <td><?php echo e($product->stock); ?></td>
            <td><?php echo e($product->is_active ? '✅' : '❌'); ?></td>
            <td>
                <form method="POST" action="<?php echo e(route('admin.shop.update', $product)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="text" name="title" value="<?php echo e($product->title); ?>" size="15">
                    <input type="number" name="price_coins" value="<?php echo e($product->price_coins); ?>" size="5">
                    <input type="number" name="stock" value="<?php echo e($product->stock); ?>" size="5">
                    <input type="hidden" name="description" value="<?php echo e($product->description); ?>">
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php if($product->is_active): echo 'checked'; endif; ?>> Активний
                    </label>
                    <button type="submit">Зберегти</button>
                </form>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo e($products->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/shop-products.blade.php ENDPATH**/ ?>