<?php $__env->startSection('title', 'Адмін панель'); ?>
<?php $__env->startSection('content'); ?>
<h1>Адмін панель</h1>

<div>
    <p>Студентів: <?php echo e($totalStudents); ?></p>
    <p>Активних курсів: <?php echo e($activeCourses); ?></p>
    <p>Заявок на розгляді: <?php echo e($pendingApplications); ?></p>
    <?php if(auth()->user()->isSuperAdmin()): ?>
        <p>Запитів на виведення: <a href="<?php echo e(route('superadmin.withdrawals')); ?>"><?php echo e($pendingWithdrawals); ?></a></p>
    <?php endif; ?>
</div>

<h2>Заняття сьогодні</h2>
<?php $__currentLoopData = $todayLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <?php echo e($lesson->start_time); ?> - <?php echo e($lesson->end_time); ?>

    | <?php echo e($lesson->course->title); ?>

    | <?php echo e($lesson->teacher->full_name); ?>

</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<h2>Навігація</h2>
<ul>
    <li><a href="<?php echo e(route('admin.users')); ?>">Користувачі</a></li>
    <li><a href="<?php echo e(route('admin.locations')); ?>">Локації та аудиторії</a></li>
    <li><a href="<?php echo e(route('admin.shop.index')); ?>">Магазин</a></li>
    <?php if(auth()->user()->isSuperAdmin()): ?>
        <li><a href="<?php echo e(route('superadmin.transactions')); ?>">Всі транзакції</a></li>
        <li><a href="<?php echo e(route('superadmin.withdrawals')); ?>">Виведення коштів</a></li>
    <?php endif; ?>
</ul>

<?php if(auth()->user()->isSuperAdmin()): ?>
<h2>Останні транзакції</h2>
<table>
    <thead><tr><th>Дата</th><th>Користувач</th><th>Тип</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><?php echo e($tx->created_at->format('d.m.y H:i')); ?></td>
        <td><?php echo e($tx->user->full_name); ?></td>
        <td><?php echo e($tx->type); ?></td>
        <td><?php echo e($tx->description); ?></td>
        <td><?php echo e($tx->amount); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>