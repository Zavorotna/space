<?php $__env->startSection('title', 'Гаманець'); ?>
<?php $__env->startSection('content'); ?>
<h1>Гаманець</h1>
<p>Баланс: <strong><?php echo e($wallet->balance); ?></strong> монет</p>

<div>
    <a href="<?php echo e(route('wallet.transfer')); ?>">Переказати</a>
    <a href="<?php echo e(route('wallet.topup')); ?>">Поповнити</a>
    <a href="<?php echo e(route('wallet.withdraw')); ?>">Вивести</a>
</div>

<div>
    <a href="<?php echo e(route('bonuses.index')); ?>">Мої бонуси</a>
    <form method="POST" action="<?php echo e(route('wallet.vip')); ?>" style="display:inline">
        <?php echo csrf_field(); ?>
        <button type="submit" onclick="return confirm('VIP статус на 3 місяці за 500 монет?')">
            <?php if(auth()->user()->isVip()): ?> ⭐ VIP (до <?php echo e(auth()->user()->vip_expires_at->format('d.m.Y')); ?>)
            <?php else: ?> Купити VIP (500 монет) <?php endif; ?>
        </button>
    </form>
</div>

<h2>Транзакції</h2>
<table>
    <thead><tr><th>Дата</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><?php echo e($tx->created_at->format('d.m.y')); ?></td>
        <td><?php echo e($tx->description); ?></td>
        <td><?php echo e($tx->amount > 0 ? '+' : ''); ?><?php echo e($tx->amount); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php echo e($transactions->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/wallet/index.blade.php ENDPATH**/ ?>