<?php $__env->startSection('title', 'Сповіщення'); ?>

<?php $__env->startSection('content'); ?>
<h1>Сповіщення</h1>

<form method="POST" action="<?php echo e(route('notifications.readAll')); ?>" style="display:inline;">
    <?php echo csrf_field(); ?>
    <button type="submit">Прочитати все</button>
</form>

<hr>

<?php if($notifications->isEmpty()): ?>
    <p>Немає сповіщень.</p>
<?php else: ?>
    <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="border:1px solid #ccc; padding:10px; margin:5px 0; <?php echo e($notification->is_read ? '' : 'background:#e3f2fd;'); ?>">
        <p><strong><?php echo e($notification->title); ?></strong></p>
        <p><?php echo e($notification->body); ?></p>
        <p><?php echo e($notification->created_at->format('d.m.Y H:i')); ?></p>
        <?php if($notification->link): ?>
            <a href="<?php echo e($notification->link); ?>">Перейти</a>
        <?php endif; ?>
        <?php if(!$notification->is_read): ?>
            <form method="POST" action="<?php echo e(route('notifications.read', $notification)); ?>" style="display:inline;">
                <?php echo csrf_field(); ?>
                <button type="submit">Прочитано</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php echo e($notifications->links()); ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/notifications/index.blade.php ENDPATH**/ ?>