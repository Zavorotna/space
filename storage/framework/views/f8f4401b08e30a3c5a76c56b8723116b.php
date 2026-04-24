<?php $__env->startSection('title', $course->title); ?>
<?php $__env->startSection('content'); ?>
<h1><?php echo e($course->title); ?></h1>
<p><?php echo e($course->description); ?></p>
<p>Ціна: <?php echo e($course->price); ?> грн. в місяць</p>
<a href="#">Детальніше</a>

<h2>Успішність та прогрес курсу</h2>
<div>
    <strong><?php echo e($enrollment->pivot->success_rate); ?>%</strong> успішність
    <progress value="<?php echo e($enrollment->pivot->success_rate); ?>" max="100"></progress>
</div>

<?php if($showTelegram && $course->telegram_link): ?>
    <p>Telegram група: <a href="<?php echo e($course->telegram_link); ?>" target="_blank">Приєднатися</a></p>
<?php endif; ?>

<h2>Домашне завдання</h2>
<?php $__currentLoopData = $course->homeworkAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($hw->title); ?></strong>
    <span>(<?php echo e(['easy'=>'легка','medium'=>'середня','hard'=>'важка'][$hw->difficulty] ?? $hw->difficulty); ?>)</span>
    <span>Термін здачі: <?php echo e($hw->deadline->format('d.m')); ?></span>
    <?php $sub = $homeworkSubmissions->get($hw->id); ?>
    <?php if($sub && $sub->status === 'accepted'): ?>
        <span>✅ Прийнято</span>
    <?php elseif($sub && $sub->status === 'revision'): ?>
        <span>🔄 На доопрацювання</span>
        <a href="<?php echo e(route('homework.submitForm', $hw)); ?>">Здати</a>
    <?php else: ?>
        <a href="<?php echo e(route('homework.submitForm', $hw)); ?>">Здати</a>
    <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<h2>Тести</h2>
<?php $__currentLoopData = $course->tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($test->title); ?></strong>
    <?php $attempts = $testAttempts->get($test->id); ?>
    <?php if($attempts && $attempts->where('passed', true)->count() > 0): ?>
        <span>✅ Складено (<?php echo e($attempts->where('passed', true)->first()->score); ?>%)</span>
    <?php else: ?>
        <a href="<?php echo e(route('tests.show', $test)); ?>">Пройти</a>
    <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($course->additionalMaterials->count()): ?>
<h2>Додаткові матеріали</h2>
<?php $__currentLoopData = $course->additionalMaterials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <?php echo e($material->title); ?>

    <?php if($material->price_coins > 0): ?>
        <span><?php echo e($material->price_coins); ?> монет</span>
        <?php if(!$material->purchases()->where('user_id', auth()->id())->exists()): ?>
            <form method="POST" action="<?php echo e(route('materials.purchase', $material)); ?>" style="display:inline">
                <?php echo csrf_field(); ?>
                <button type="submit">Придбати</button>
            </form>
        <?php else: ?>
            <a href="<?php echo e($material->url); ?>" target="_blank">Відкрити</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="<?php echo e($material->url); ?>" target="_blank">Відкрити</a>
    <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php if(!$enrollment->pivot->review_submitted): ?>
<h2>Залишити відгук</h2>
<form method="POST" action="<?php echo e(route('courses.review', $course)); ?>">
    <?php echo csrf_field(); ?>
    <select name="rating" required>
        <option value="5">5 - Відмінно</option>
        <option value="4">4 - Добре</option>
        <option value="3">3 - Нормально</option>
        <option value="2">2 - Погано</option>
        <option value="1">1 - Жахливо</option>
    </select>
    <textarea name="text" placeholder="Ваш відгук..."></textarea>
    <button type="submit">Надіслати (+100 монет)</button>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/student/course.blade.php ENDPATH**/ ?>