<?php $__env->startSection('title', $user->last_name . ' ' . $user->first_name); ?>

<?php $__env->startSection('content'); ?>
<div>
    <?php if($user->getFirstMediaUrl('avatar')): ?>
        <img src="<?php echo e($user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" class="avatar avatar-lg">
    <?php endif; ?>

    <h1><?php echo e($user->last_name); ?> <?php echo e($user->first_name); ?>

        <?php if($user->isVip()): ?> ⭐ VIP <?php endif; ?>
    </h1>
    <p class="text-muted">
        <?php switch($user->role):
            case ('admin'): ?> Адміністратор <?php break; ?>
            <?php case ('superadmin'): ?> Суперадмін <?php break; ?>
            <?php case ('registered'): ?> Зареєстрований <?php break; ?>
            <?php default: ?> <?php echo e($user->role); ?>

        <?php endswitch; ?>
    </p>
    <?php if($user->login_streak > 0): ?>
        <p>Серія входів: <?php echo e($user->login_streak); ?> днів</p>
    <?php endif; ?>
</div>

<?php if($user->bio): ?>
    <h2>Про себе</h2>
    <p><?php echo nl2br(e($user->bio)); ?></p>
<?php endif; ?>

<?php if($user->achievements->count()): ?>
    <h2>Досягнення</h2>
    <ul>
    <?php $__currentLoopData = $user->achievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($achievement->title); ?></li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/profile/generic.blade.php ENDPATH**/ ?>