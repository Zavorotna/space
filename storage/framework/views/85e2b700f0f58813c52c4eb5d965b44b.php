<?php $__env->startSection('title', 'Дашборд студента'); ?>
<?php $__env->startSection('content'); ?>
<h1>Дашборд</h1>

<?php echo $__env->make('partials._admin_banners', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($currentCourse): ?>
<div class="mb-3">
    <h2><?php echo e($currentCourse->title); ?></h2>
    <p><?php echo e($currentCourse->description); ?></p>
    <p>Успішність: <?php echo e($currentCourse->pivot->success_rate); ?>%</p>
    <progress value="<?php echo e($currentCourse->pivot->success_rate); ?>" max="100"></progress>
    <p>Викладач: <?php echo e($currentCourse->teacher->full_name); ?></p>
    <a href="<?php echo e(route('courses.student.show', $currentCourse)); ?>">Детальніше</a>
</div>
<?php else: ?>
<p>Ви не записані на жодний активний курс. <a href="<?php echo e(route('courses.public')); ?>">Переглянути курси</a></p>
<?php endif; ?>


<?php echo $__env->make('partials._calendar', [
    'schedDate'    => $schedDate,
    'schedMode'    => $schedMode,
    'schedLessons' => $schedLessons,
    'schedEvents'  => $schedEvents,
    'canEdit'      => false,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<h2>Домашні завдання</h2>
<p>Здати: <?php echo e($totalHomeworkToDo); ?> | На доопрацювання: <?php echo e($pendingHomework); ?></p>

<h2>Замітки</h2>
<?php $__currentLoopData = $receivedNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($note->author->full_name); ?>:</strong> <?php echo e($note->content); ?>

    <form method="POST" action="<?php echo e(route('notes.read', $note)); ?>" class="form-inline">
        <?php echo csrf_field(); ?>
        <button type="submit">Прочитано</button>
    </form>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div><?php echo e($note->content); ?></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<form method="POST" action="<?php echo e(route('notes.store')); ?>">
    <?php echo csrf_field(); ?>
    <textarea name="content" placeholder="Нова замітка..." required></textarea>
    <button type="submit">Зберегти</button>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong><?php echo e($wallet->balance); ?></strong> монет</p>
<a href="<?php echo e(route('wallet.transfer')); ?>">Переказати</a>
<a href="<?php echo e(route('wallet.topup')); ?>">Поповнити</a>
<a href="<?php echo e(route('wallet.withdraw')); ?>">Вивести</a>

<h3>Транзакції</h3>
<?php if($transactions->isEmpty()): ?>
<p class="text-subtle">Немає транзакцій.</p>
<?php else: ?>
<table class="data-table">
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
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/student/dashboard.blade.php ENDPATH**/ ?>