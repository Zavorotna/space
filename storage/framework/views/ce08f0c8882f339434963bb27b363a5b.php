<?php $__env->startSection('title', 'Перевірка ДЗ: ' . $homework->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.edit', $homework->course_id)); ?>">&larr; Назад до курсу</a>

<h1>Здачі: <?php echo e($homework->title); ?></h1>
<p>Складність: <?php echo e(['easy' => 'Легка', 'medium' => 'Середня', 'hard' => 'Важка'][$homework->difficulty]); ?> |
   Дедлайн: <?php echo e(\Carbon\Carbon::parse($homework->deadline)->format('d.m.Y')); ?></p>

<?php if($submissions->isEmpty()): ?>
    <p>Ще немає здач.</p>
<?php else: ?>
    <?php $__currentLoopData = $submissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
        <h3><?php echo e($sub->user->last_name); ?> <?php echo e($sub->user->first_name); ?></h3>
        <p>Статус:
            <?php switch($sub->status):
                case ('submitted'): ?> 🟡 На перевірці <?php break; ?>
                <?php case ('accepted'): ?> ✅ Прийнято <?php break; ?>
                <?php case ('revision'): ?> ⚠️ На доопрацювання <?php break; ?>
            <?php endswitch; ?>
        </p>
        <p>Здано: <?php echo e($sub->submitted_at ? $sub->submitted_at->format('d.m.Y H:i') : '—'); ?></p>
        <p>Доопрацювань: <?php echo e($sub->revision_count); ?></p>
        <?php if($sub->early_submission): ?> <p>🎯 Рання здача (+10 монет)</p> <?php endif; ?>

        
        <?php if($sub->getMedia('files')->count()): ?>
            <h4>Файли:</h4>
            <?php $__currentLoopData = $sub->getMedia('files'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="display:inline-block; margin:5px;">
                    <a href="<?php echo e($media->getUrl()); ?>" target="_blank">
                        <img src="<?php echo e($media->getUrl()); ?>" alt="<?php echo e($media->file_name); ?>" style="max-width:150px; max-height:150px;">
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        
        <?php if($sub->links && count($sub->links)): ?>
            <h4>Посилання:</h4>
            <ul>
            <?php $__currentLoopData = $sub->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><a href="<?php echo e($link); ?>" target="_blank"><?php echo e($link); ?></a></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>

        
        <?php if($sub->status === 'submitted'): ?>
        <form method="POST" action="<?php echo e(route('teacher.homework.review', $sub)); ?>">
            <?php echo csrf_field(); ?>
            <div>
                <label>Коментар</label>
                <textarea name="teacher_comment" rows="2"><?php echo e($sub->teacher_comment); ?></textarea>
            </div>
            <div>
                <button type="submit" name="status" value="accepted">✅ Прийняти</button>
                <button type="submit" name="status" value="revision">⚠️ На доопрацювання (-1 монета)</button>
            </div>
        </form>
        <?php elseif($sub->teacher_comment): ?>
            <p><strong>Коментар:</strong> <?php echo e($sub->teacher_comment); ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/homework/submissions.blade.php ENDPATH**/ ?>