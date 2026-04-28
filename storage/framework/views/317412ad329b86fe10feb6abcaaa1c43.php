<?php $__env->startSection('title', 'Батько: ' . $user->last_name . ' ' . $user->first_name); ?>

<?php $__env->startSection('content'); ?>
<div>
    <?php if($user->getFirstMediaUrl('avatar')): ?>
        <img src="<?php echo e($user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" class="avatar avatar-lg">
    <?php endif; ?>

    <h1><?php echo e($user->last_name); ?> <?php echo e($user->first_name); ?>

        <?php if($user->isVip()): ?> ⭐ VIP <?php endif; ?>
    </h1>
    <p>Батько/Мати</p>
    <?php if($user->login_streak > 0): ?>
        <p>Серія входів: <?php echo e($user->login_streak); ?> днів</p>
    <?php endif; ?>
</div>


<h2>Діти</h2>
<?php if($user->children->count()): ?>
    <?php $__currentLoopData = $user->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <a href="<?php echo e(route('profile.show', $child)); ?>"><?php echo e($child->last_name); ?> <?php echo e($child->first_name); ?></a>
            <span>(<?php echo e($child->role); ?>)</span>

            <?php if(auth()->user()->isAdmin()): ?>
                <form method="POST" action="<?php echo e(route('admin.users.unlinkChild', [$user, $child])); ?>" class="form-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" onclick="return confirm('Скасувати зв\'язок з <?php echo e($child->first_name); ?>?')">
                        Скасувати зв'язок
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
    <p>Дітей не додано.</p>
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


<?php if(auth()->check() && auth()->id() !== $user->id && (auth()->user()->isAdmin() || auth()->user()->isTeacher())): ?>
<div class="notify-form">
    <h2>Надіслати повідомлення</h2>
    <?php if(session('notify_success')): ?>
    <p class="text-success mb-1"><?php echo e(session('notify_success')); ?></p>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('notifications.sendToUser', $user)); ?>">
        <?php echo csrf_field(); ?>
        <textarea name="message" rows="3" required placeholder="Текст повідомлення..."></textarea>
        <button type="submit" class="btn-submit">Надіслати</button>
    </form>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/profile/parent.blade.php ENDPATH**/ ?>