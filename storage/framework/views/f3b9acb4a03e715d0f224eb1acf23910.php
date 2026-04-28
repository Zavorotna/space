<?php $__env->startSection('title', 'Здача ДЗ: ' . $homework->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('courses.student.show', $homework->course_id)); ?>">&larr; Назад до курсу</a>

<h1><?php echo e($homework->title); ?></h1>

<div>
    <p><strong>Складність:</strong>
        <?php echo e(['easy' => 'Легка (+5)', 'medium' => 'Середня (+15)', 'hard' => 'Важка (+25)'][$homework->difficulty]); ?>

    </p>
    <p><strong>Дедлайн:</strong> <?php echo e(\Carbon\Carbon::parse($homework->deadline)->format('d.m.Y')); ?></p>
    <?php if($homework->description): ?>
        <div>
            <h3>Завдання</h3>
            <?php echo nl2br(e($homework->description)); ?>

        </div>
    <?php endif; ?>

    
    <?php if($homework->getMedia('attachments')->count()): ?>
        <h3>Прикріплені файли</h3>
        <ul>
        <?php $__currentLoopData = $homework->getMedia('attachments'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><a href="<?php echo e($media->getUrl()); ?>" target="_blank"><?php echo e($media->file_name); ?></a></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</div>

<hr>


<?php if($submission && $submission->exists): ?>
    <div>
        <h3>Поточний статус</h3>
        <p>Статус:
            <?php switch($submission->status):
                case ('submitted'): ?> На перевірці <?php break; ?>
                <?php case ('accepted'): ?> ✅ Прийнято <?php break; ?>
                <?php case ('revision'): ?> ⚠️ На доопрацювання <?php break; ?>
                <?php default: ?> <?php echo e($submission->status); ?>

            <?php endswitch; ?>
        </p>

        <?php if($submission->teacher_comment): ?>
            <p><strong>Коментар викладача:</strong> <?php echo e($submission->teacher_comment); ?></p>
        <?php endif; ?>

        <?php if($submission->revision_count > 0): ?>
            <p>Кількість доопрацювань: <?php echo e($submission->revision_count); ?></p>
        <?php endif; ?>

        
        <?php if($submission->getMedia('files')->count()): ?>
            <h4>Завантажені файли</h4>
            <?php $__currentLoopData = $submission->getMedia('files'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <img src="<?php echo e($media->getUrl()); ?>" alt="<?php echo e($media->file_name); ?>" class="thumb-lg">
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        
        <?php if($submission->status !== 'accepted'): ?>
        <div>
            <h4>Заморозка дедлайну</h4>
            <p>Ефективний дедлайн: <?php echo e($submission->effective_deadline ? \Carbon\Carbon::parse($submission->effective_deadline)->format('d.m.Y') : \Carbon\Carbon::parse($homework->deadline)->format('d.m.Y')); ?></p>
            <p>Використано днів заморозки: <?php echo e($submission->frozen_days ?? 0); ?> / 5</p>
            <form method="POST" action="<?php echo e(route('homework.freeze', $submission)); ?>" class="form-inline">
                <?php echo csrf_field(); ?>
                <label>Днів:
                    <select name="days">
                        <?php for($d = 1; $d <= 5 - ($submission->frozen_days ?? 0); $d++): ?>
                            <option value="<?php echo e($d); ?>"><?php echo e($d); ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <button type="submit">Заморозити (-15 монет/день)</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>


<?php if(!$submission || $submission->status !== 'accepted'): ?>
<hr>
<h2><?php echo e($submission && $submission->exists ? 'Повторна здача' : 'Здати домашку'); ?></h2>

<form method="POST" action="<?php echo e(route('homework.submit', $homework)); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <div>
        <label>Завантаження файлу</label>
        <p>Формат: PNG, JPEG, WebP. Максимальний розмір: 2 МБ</p>
        <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp">
    </div>

    <div>
        <label>Посилання (кожне з нового рядка)</label>
        <textarea name="links" rows="3" placeholder="https://docs.google.com/spreadsheets/d/..."><?php echo e(old('links', $submission?->links ? implode("\n", $submission->links) : '')); ?></textarea>
    </div>

    <div>
        <label>Або одне посилання</label>
        <input type="url" name="link_url" placeholder="https://...">
    </div>

    <button type="submit">Відправити</button>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/homework/submit.blade.php ENDPATH**/ ?>