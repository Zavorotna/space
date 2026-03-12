<?php $__env->startSection('title', 'Курси'); ?>
<?php $__env->startSection('content'); ?>
<h1>Курси</h1>
<?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <?php if($course->getFirstMediaUrl('cover')): ?>
        <img src="<?php echo e($course->getFirstMediaUrl('cover')); ?>" alt="<?php echo e($course->title); ?>" width="200">
    <?php endif; ?>
    <h2><a href="<?php echo e(route('courses.detail', $course)); ?>"><?php echo e($course->title); ?></a></h2>
    <p><?php echo e(Str::limit($course->description, 150)); ?></p>
    <p>Викладач: <?php echo e($course->teacher->full_name); ?></p>
    <p>Ціна: <?php echo e($course->price); ?> грн/<?php echo e($course->billing_period === 'monthly' ? 'міс' : 'разово'); ?></p>
    <p>Статус: <?php echo e($course->status); ?></p>
</div>
<hr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php echo e($courses->links()); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/public/courses.blade.php ENDPATH**/ ?>