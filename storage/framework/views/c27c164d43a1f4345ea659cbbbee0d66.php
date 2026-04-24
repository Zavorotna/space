<?php $__env->startSection('title', 'Всі транзакції'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('dashboard')); ?>">&larr; Дашборд</a>

<h1>Всі транзакції</h1>


<form method="GET" action="<?php echo e(route('superadmin.transactions')); ?>">
    <select name="type">
        <option value="">— Всі типи —</option>
        <?php $__currentLoopData = ['reward','penalty','deposit','withdrawal','transfer','purchase','course_payment','resume_purchase','donation','bonus_purchase','bonus_sell']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($t); ?>" <?php if(request('type') === $t): echo 'selected'; endif; ?>><?php echo e($t); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <input type="number" name="user_id" placeholder="ID користувача" value="<?php echo e(request('user_id')); ?>">
    <button type="submit">Фільтрувати</button>
</form>

<hr>

<table>
    <thead>
        <tr>
            <th>Дата</th><th>Користувач</th><th>Тип</th><th>Сума</th><th>Призначення</th><th>Кому</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($tx->created_at->format('d.m.Y H:i')); ?></td>
            <td><?php echo e($tx->user->last_name ?? ''); ?> <?php echo e($tx->user->first_name ?? ''); ?> (#<?php echo e($tx->user_id); ?>)</td>
            <td><?php echo e($tx->type); ?></td>
            <td style="color:<?php echo e($tx->amount > 0 ? 'green' : 'red'); ?>"><?php echo e($tx->amount > 0 ? '+' : ''); ?><?php echo e($tx->amount); ?></td>
            <td><?php echo e($tx->description); ?></td>
            <td>
                <?php if($tx->relatedUser): ?>
                    <?php echo e($tx->relatedUser->last_name ?? ''); ?> <?php echo e($tx->relatedUser->first_name ?? ''); ?>

                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo e($transactions->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/transactions.blade.php ENDPATH**/ ?>