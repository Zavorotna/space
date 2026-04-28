<?php $__env->startSection('title', 'Випускний проєкт: ' . $project->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.edit', $project->course_id)); ?>">&larr; Назад до курсу</a>

<h1>Випускний проєкт: <?php echo e($project->title); ?></h1>
<p>Дедлайн: <?php echo e(\Carbon\Carbon::parse($project->deadline)->format('d.m.Y')); ?></p>
<?php if($project->description): ?>
    <p><?php echo e($project->description); ?></p>
<?php endif; ?>

<?php if($submissions->isEmpty()): ?>
    <p>Ще немає здач.</p>
<?php else: ?>
    <?php $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card-panel">
        <h3><?php echo e($sub->user->last_name); ?> <?php echo e($sub->user->first_name); ?></h3>
        <p>Статус:
            <?php switch($sub->status):
                case ('submitted'): ?> 🟡 На перевірці <?php break; ?>
                <?php case ('accepted'): ?> ✅ Захищено <?php break; ?>
                <?php case ('revision'): ?> ⚠️ На доопрацювання <?php break; ?>
                <?php case ('commission'): ?> 🔶 На комісію <?php break; ?>
            <?php endswitch; ?>
        </p>
        <p>Здано: <?php echo e($sub->submitted_at ? $sub->submitted_at->format('d.m.Y H:i') : '—'); ?></p>
        <p>Доопрацювань: <?php echo e($sub->revision_count); ?> | Нагорода: <?php echo e($sub->calculateReward()); ?> монет</p>

        
        <?php if($sub->getMedia('files')->count()): ?>
            <h4>Файли:</h4>
            <?php $__currentLoopData = $sub->getMedia('files'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($media->getUrl()); ?>" target="_blank"><?php echo e($media->file_name); ?></a><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        
        <?php if($sub->links && count($sub->links)): ?>
            <h4>Посилання:</h4>
            <?php $__currentLoopData = $sub->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e($link); ?>" target="_blank"><?php echo e($link); ?></a><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        
        <?php if($sub->status === 'submitted' || $sub->status === 'commission'): ?>
        <form method="POST" action="<?php echo e(route('teacher.graduation.review', $sub)); ?>">
            <?php echo csrf_field(); ?>
            <div>
                <label>Коментар</label>
                <textarea name="teacher_comment" rows="2"><?php echo e($sub->teacher_comment); ?></textarea>
            </div>
            <div>
                <button type="submit" name="status" value="accepted">✅ Захищено</button>
                <button type="submit" name="status" value="revision">⚠️ На доопрацювання (-5 монет)</button>
                <button type="submit" name="status" value="commission">🔶 На комісію</button>
            </div>
        </form>
        <?php elseif($sub->teacher_comment): ?>
            <p><strong>Коментар:</strong> <?php echo e($sub->teacher_comment); ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/project/submissions.blade.php ENDPATH**/ ?>