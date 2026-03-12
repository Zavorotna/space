<?php $__env->startSection('title', 'Дашборд викладача'); ?>
<?php $__env->startSection('content'); ?>
<h1>Розклад занять</h1>

<table>
    <thead><tr><th>День</th><th>10:00</th><th>12:00</th><th>14:00</th><th>16:00</th><th>18:00</th></tr></thead>
    <tbody>
    <?php
        $weekDays = $weekSchedule->groupBy(fn($l) => $l->date->format('D d.m'));
    ?>
    <?php $__currentLoopData = $weekDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day => $lessons): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><?php echo e($day); ?></td>
        <?php $__currentLoopData = ['10:00','12:00','14:00','16:00','18:00']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <td>
            <?php $match = $lessons->first(fn($l) => $l->start_time <= $time && $l->end_time > $time); ?>
            <?php echo e($match ? $match->course->title : ''); ?>

        </td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<a href="<?php echo e(route('schedule.index')); ?>">Переглянути повний розклад</a>

<h2>Запити на заняття</h2>
<?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($course->applications()->where('status','pending')->count() > 0): ?>
        <a href="<?php echo e(route('teacher.courses.applications', $course)); ?>">
            <?php echo e($course->title); ?>: <?php echo e($course->applications()->where('status','pending')->count()); ?> заявок
        </a>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<h2>Прогрес курсів (групи)</h2>
<?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($course->title); ?></strong>
    <?php
        $progress = 0;
        if ($course->start_date && $course->end_date) {
            $total = $course->start_date->diffInDays($course->end_date);
            $elapsed = $course->start_date->diffInDays(now());
            $progress = $total > 0 ? min(100, round($elapsed / $total * 100)) : 0;
        }
    ?>
    <?php echo e($progress); ?>%
    <progress value="<?php echo e($progress); ?>" max="100"></progress>
    <span><?php echo e($course->start_date?->format('d.m')); ?> — <?php echo e($course->end_date?->format('d.m')); ?></span>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($pendingHomework > 0): ?>
<p>Домашок на перевірку: <strong><?php echo e($pendingHomework); ?></strong></p>
<?php endif; ?>

<h2>Замітки</h2>
<?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div><?php echo e($note->content); ?></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<form method="POST" action="<?php echo e(route('notes.store')); ?>">
    <?php echo csrf_field(); ?>
    <textarea name="content" placeholder="Нова замітка..." required></textarea>
    <button type="submit">Зберегти</button>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong><?php echo e($wallet->balance); ?></strong></p>
<a href="<?php echo e(route('wallet.transfer')); ?>">переказати</a>
<a href="<?php echo e(route('wallet.topup')); ?>">поповнити</a>
<a href="<?php echo e(route('wallet.withdraw')); ?>">вивести</a>

<h3>Транзакції</h3>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/teacher/dashboard.blade.php ENDPATH**/ ?>