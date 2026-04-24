<?php $__env->startSection('title', 'Викладач: ' . $user->last_name . ' ' . $user->first_name); ?>

<?php $__env->startSection('content'); ?>
<div>
    <?php if($user->getFirstMediaUrl('avatar')): ?>
        <img src="<?php echo e($user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    <?php endif; ?>

    <h1><?php echo e($user->last_name); ?> <?php echo e($user->first_name); ?>

        <?php if($user->isVip()): ?> ⭐ <?php endif; ?>
    </h1>
    <p>Викладач</p>
</div>

<?php if($user->bio): ?>
    <h2>Про мене</h2>
    <p><?php echo nl2br(e($user->bio)); ?></p>
<?php endif; ?>


<h2>Курси</h2>
<?php if($user->taughtCourses->count()): ?>
    <?php $__currentLoopData = $user->taughtCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div style="border:1px solid #ccc; padding:10px; margin:5px 0;">
            <h3><?php echo e($course->title); ?></h3>
            <p><?php echo e(Str::limit($course->description, 100)); ?></p>
            <a href="<?php echo e(route('courses.detail', $course)); ?>">Детальніше</a>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
    <p>Наразі немає активних курсів.</p>
<?php endif; ?>


<?php if($user->achievements->count()): ?>
    <h2>Досягнення</h2>
    <ul>
    <?php $__currentLoopData = $user->achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($achievement->title); ?> — <?php echo e($achievement->description); ?></li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/profile/teacher.blade.php ENDPATH**/ ?>