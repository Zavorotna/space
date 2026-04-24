<?php $__env->startSection('title', 'Резюме студентів'); ?>

<?php $__env->startSection('content'); ?>
<h1>Резюме студентів</h1>

<?php if($resumes->isEmpty()): ?>
    <p>Наразі немає опублікованих резюме.</p>
<?php else: ?>
    <?php $__currentLoopData = $resumes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resume): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
        <div>
            <?php if($resume->user->getFirstMediaUrl('avatar')): ?>
                <img src="<?php echo e($resume->user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" style="width:60px; height:60px; border-radius:50%;">
            <?php endif; ?>
            <h3><?php echo e($resume->user->last_name); ?> <?php echo e($resume->user->first_name); ?>

                <?php if($resume->user->isVip()): ?> ⭐ <?php endif; ?>
            </h3>
        </div>

        
        <?php if($resume->user->certificates->count()): ?>
            <p>Курси:
            <?php $__currentLoopData = $resume->user->certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span><?php echo e($cert->course->title ?? '—'); ?> (<?php echo e($cert->success_rate); ?>%)</span><?php if(!$loop->last): ?>, <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </p>
        <?php endif; ?>

        <?php if($resume->about): ?>
            <p><?php echo e(Str::limit($resume->about, 150)); ?></p>
        <?php endif; ?>

        <a href="<?php echo e(route('resumes.show', $resume)); ?>">Детальніше</a>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo e($resumes->links()); ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/resume/index.blade.php ENDPATH**/ ?>