<?php $__env->startSection('title', 'Запити на виведення'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('dashboard')); ?>">&larr; Дашборд</a>

<h1>Запити на виведення монет</h1>


<form method="GET" action="<?php echo e(route('superadmin.withdrawals')); ?>">
    <select name="status">
        <option value="">— Всі статуси —</option>
        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>Очікує</option>
        <option value="approved" <?php if(request('status') === 'approved'): echo 'selected'; endif; ?>>Підтверджено</option>
        <option value="rejected" <?php if(request('status') === 'rejected'): echo 'selected'; endif; ?>>Відхилено</option>
    </select>
    <button type="submit">Фільтрувати</button>
</form>

<hr>

<?php if($requests->isEmpty()): ?>
    <p>Немає запитів.</p>
<?php else: ?>
<table>
    <thead>
        <tr><th>Дата</th><th>Користувач</th><th>Сума</th><th>Статус</th><th>Дії</th></tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($req->created_at->format('d.m.Y H:i')); ?></td>
            <td><?php echo e($req->user->last_name ?? ''); ?> <?php echo e($req->user->first_name ?? ''); ?></td>
            <td><?php echo e($req->amount); ?></td>
            <td>
                <?php switch($req->status):
                    case ('pending'): ?> 🟡 Очікує <?php break; ?>
                    <?php case ('approved'): ?> ✅ Підтверджено <?php break; ?>
                    <?php case ('rejected'): ?> ❌ Відхилено <?php break; ?>
                <?php endswitch; ?>
            </td>
            <td>
                <?php if($req->status === 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('superadmin.withdrawals.approve', $req)); ?>" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <input type="text" name="pickup_note" placeholder="Куди підійти для отримання" required>
                        <button type="submit">Підтвердити</button>
                    </form>
                    <form method="POST" action="<?php echo e(route('superadmin.withdrawals.reject', $req)); ?>" style="display:inline;"
                          onsubmit="return confirm('Відхилити? Монети повернуться користувачу.')">
                        <?php echo csrf_field(); ?>
                        <button type="submit">Відхилити</button>
                    </form>
                <?php endif; ?>
                <?php if($req->pickup_note): ?>
                    <p>Примітка: <?php echo e($req->pickup_note); ?></p>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo e($requests->links()); ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/withdrawals.blade.php ENDPATH**/ ?>