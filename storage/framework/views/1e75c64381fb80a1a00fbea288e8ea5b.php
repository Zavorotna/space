<?php $__env->startSection('title', 'Замітки'); ?>

<?php $__env->startSection('content'); ?>
<h1>Замітки</h1>


<h2>Нова замітка</h2>
<form method="POST" action="<?php echo e(route('notes.store')); ?>">
    <?php echo csrf_field(); ?>
    <div>
        <textarea name="content" rows="3" placeholder="Текст замітки..." required></textarea>
    </div>

    
    <?php if(auth()->user()->isTeacher() || auth()->user()->isAdmin()): ?>
    <div>
        <label>Надіслати студенту (необов'язково)</label>
        <input type="number" name="recipient_id" placeholder="ID студента">
    </div>
    <div>
        <label>Курс (необов'язково)</label>
        <select name="course_id">
            <option value="">— без курсу —</option>
            <?php $__currentLoopData = auth()->user()->taughtCourses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($course->id); ?>"><?php echo e($course->title); ?><?php echo e($course->is_template ? ' (шаблон)' : ''); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>

    <button type="submit">Зберегти</button>
</form>

<hr>


<?php if($receivedNotes->count()): ?>
<h2>Замітки від викладача</h2>
<?php $__currentLoopData = $receivedNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="border:1px solid #ccc; padding:10px; margin:5px 0; <?php echo e($note->is_read ? '' : 'background:#fffde7;'); ?>">
        <p><strong><?php echo e($note->author->last_name ?? ''); ?> <?php echo e($note->author->first_name ?? ''); ?></strong>
            — <?php echo e($note->created_at->format('d.m.Y H:i')); ?></p>
        <p><?php echo nl2br(e($note->content)); ?></p>
        <?php if(!$note->is_read): ?>
            <form method="POST" action="<?php echo e(route('notes.read', $note)); ?>" style="display:inline;">
                <?php echo csrf_field(); ?>
                <button type="submit">Прочитано</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<h2>Мої замітки</h2>
<?php if($personalNotes->isEmpty()): ?>
    <p>У вас ще немає заміток.</p>
<?php else: ?>
    <?php $__currentLoopData = $personalNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="border:1px solid #eee; padding:10px; margin:5px 0;">
        <p><?php echo e($note->created_at->format('d.m.Y H:i')); ?></p>
        <form method="POST" action="<?php echo e(route('notes.update', $note)); ?>" style="display:inline;">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <textarea name="content" rows="2"><?php echo e($note->content); ?></textarea>
            <button type="submit">Оновити</button>
        </form>
        <form method="POST" action="<?php echo e(route('notes.destroy', $note)); ?>" style="display:inline;"
              onsubmit="return confirm('Видалити?')">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit">Видалити</button>
        </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/notes/index.blade.php ENDPATH**/ ?>