<?php $__env->startSection('title', 'Студент: ' . $user->last_name . ' ' . $user->first_name); ?>

<?php $__env->startSection('content'); ?>
<div>
    <?php if($user->getFirstMediaUrl('avatar')): ?>
        <img src="<?php echo e($user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    <?php endif; ?>

    <h1><?php echo e($user->last_name); ?> <?php echo e($user->first_name); ?>

        <?php if($user->isVip()): ?> ⭐ VIP <?php endif; ?>
    </h1>
    <p>Студент</p>
    <?php if($user->login_streak > 0): ?>
        <p>Серія входів: <?php echo e($user->login_streak); ?> днів</p>
    <?php endif; ?>
</div>


<?php if($user->parents->count()): ?>
    <h2>Батьки</h2>
    <?php $__currentLoopData = $user->parents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <a href="<?php echo e(route('profile.show', $parent)); ?>"><?php echo e($parent->last_name); ?> <?php echo e($parent->first_name); ?></a>
            <?php if($parent->phone): ?> — <?php echo e($parent->phone); ?> <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if($user->enrollments->count()): ?>
    <h2>Курси</h2>
    <?php $__currentLoopData = $user->enrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <strong><?php echo e($course->title); ?></strong>
            —
            <?php switch($course->pivot->status):
                case ('active'): ?> Активний <?php break; ?>
                <?php case ('completed'): ?> Завершений <?php break; ?>
                <?php case ('pending'): ?> Очікує <?php break; ?>
                <?php default: ?> <?php echo e($course->pivot->status); ?>

            <?php endswitch; ?>
            <?php if($course->start_date || $course->end_date): ?>
                (<?php echo e($course->start_date?->format('d.m.Y') ?? '?'); ?> — <?php echo e($course->end_date?->format('d.m.Y') ?? '?'); ?>)
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if($user->certificates->count()): ?>
    <h2>Сертифікати</h2>
    <?php $__currentLoopData = $user->certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <p><?php echo e($cert->course->title ?? '—'); ?> — <?php echo e($cert->success_rate); ?>%
                (<?php switch($cert->type):
                    case ('bw'): ?> ЧБ <?php break; ?>
                    <?php case ('color'): ?> Кольоровий <?php break; ?>
                    <?php case ('guaranteed'): ?> З гарантією <?php break; ?>
                <?php endswitch; ?>)
            </p>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if($user->achievements->count()): ?>
    <h2>Досягнення</h2>
    <ul>
    <?php $__currentLoopData = $user->achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($achievement->title); ?></li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
<?php endif; ?>


<?php if(auth()->guard()->check()): ?>
    <?php if(auth()->id() !== $user->id): ?>
        <p><a href="<?php echo e(route('wallet.transfer')); ?>?to=<?php echo e($user->id); ?>">Переказати монети</a></p>
    <?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/profile/student.blade.php ENDPATH**/ ?>