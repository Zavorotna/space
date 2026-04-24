<?php $__env->startSection('title', 'Досягнення'); ?>

<?php $__env->startSection('content'); ?>
<h1>Досягнення</h1>


<h2>Топ місяця — <?php echo e(now()->translatedFormat('F Y')); ?></h2>
<?php if($leaderboard->count()): ?>
    <table>
        <thead>
            <tr><th>#</th><th>Студент</th><th>Бали</th><th>Монети</th></tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($entry->rank); ?></td>
                <td><?php echo e($entry->user->last_name ?? ''); ?> <?php echo e($entry->user->first_name ?? ''); ?></td>
                <td><?php echo e($entry->total_score); ?></td>
                <td>
                    <?php if($entry->rank === 1): ?> +50
                    <?php elseif($entry->rank === 2): ?> +30
                    <?php elseif($entry->rank === 3): ?> +20
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Дані за поточний місяць ще не розраховані.</p>
<?php endif; ?>

<hr>


<h2>Всі досягнення</h2>

<?php $__currentLoopData = $allAchievements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $achievement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div style="border:1px solid #ccc; padding:10px; margin:5px 0; <?php echo e(in_array($achievement->id, $earned) ? 'background:#e8f5e9;' : ''); ?>">
    <h3><?php echo e($achievement->title); ?> <?php echo e(in_array($achievement->id, $earned) ? '✅' : '🔒'); ?></h3>
    <p><?php echo e($achievement->description); ?></p>
    <p>Нагорода: +<?php echo e($achievement->reward_coins); ?> монет</p>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/achievements/index.blade.php ENDPATH**/ ?>