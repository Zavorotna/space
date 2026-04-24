<?php $__env->startSection('title', $course->title); ?>
<?php $__env->startSection('content'); ?>
<h1><?php echo e($course->title); ?></h1>
<?php if($course->getFirstMediaUrl('cover')): ?>
    <img src="<?php echo e($course->getFirstMediaUrl('cover')); ?>" alt="<?php echo e($course->title); ?>" width="300">
<?php endif; ?>
<p><?php echo e($course->description); ?></p>
<?php if($course->program): ?>
    <h2>Програма</h2>
    <div><?php echo nl2br(e($course->program)); ?></div>
<?php endif; ?>
<p>Викладач: <?php echo e($course->teacher->full_name); ?></p>
<p>Ціна: <?php echo e($course->price); ?> грн/<?php echo e(['monthly' => 'міс', 'one_time' => 'разово', 'per_lesson' => 'заняття'][$course->billing_period] ?? $course->billing_period); ?></p>
<p>Дата початку: <?php echo e($course->start_date?->format('d.m.Y') ?? 'Не визначено'); ?></p>
<p>Статус: <?php echo e($course->status); ?></p>

<?php if(auth()->guard()->check()): ?>
    <?php if(!$course->students()->where('user_id', auth()->id())->exists()): ?>
        <form method="POST" action="<?php echo e(route('courses.apply', $course)); ?>">
            <?php echo csrf_field(); ?>
            <textarea name="note" placeholder="Коментар до заявки"></textarea>
            <button type="submit">Подати заявку</button>
        </form>
    <?php else: ?>
        <a href="<?php echo e(route('courses.student.show', $course)); ?>">Перейти до курсу</a>
    <?php endif; ?>
<?php endif; ?>

<h2>Відгуки</h2>
<?php $__currentLoopData = $course->reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($review->user->full_name); ?></strong> — <?php echo e($review->rating); ?>/5
    <p><?php echo e($review->text); ?></p>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/public/course-detail.blade.php ENDPATH**/ ?>