<?php $__env->startSection('title', 'Поповнення балансу'); ?>
<?php $__env->startSection('content'); ?>
<h1>Поповнення балансу</h1>
<p>Баланс: <strong><?php echo e($wallet->balance); ?></strong></p>

<h2>Оберіть спосіб поповнення</h2>
<form method="POST" action="<?php echo e(route('wallet.topup.process')); ?>">
    <?php echo csrf_field(); ?>
    <div>
        <label><input type="radio" name="method" value="card" checked> З банківської картки</label>
    </div>
    <div><label>Сума</label><input type="number" name="amount" min="1" placeholder="Введіть суму" required></div>
    <div>
        <?php $__currentLoopData = [100,200,300,400,500]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $amt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" onclick="document.querySelector('[name=amount]').value=<?php echo e($amt); ?>"><?php echo e($amt); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <button type="submit">Підтвердити</button>
</form>

<h3>Транзакції</h3>
<?php if($transactions->isEmpty()): ?>
    <p>Транзакцій ще немає.</p>
<?php else: ?>
<table>
    <thead><tr><th>Дата</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr><td><?php echo e($tx->created_at->format('d.m.y')); ?></td><td><?php echo e($tx->description); ?></td><td><?php echo e($tx->amount); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/wallet/topup.blade.php ENDPATH**/ ?>