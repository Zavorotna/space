<?php $__env->startSection('title', 'Результат тесту'); ?>
<?php $__env->startSection('content'); ?>
<h1><?php echo e($attempt->passed ? 'Вітаємо! Ви успішно склали тест' : 'Тест не складено'); ?></h1>
<h2><?php echo e($test->title); ?></h2>
<p>Ваш бал за тест: <strong><?php echo e($attempt->score); ?>%</strong></p>

<div>
    <?php $__currentLoopData = $test->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $answer = $attempt->answers->firstWhere('question_id', $question->id);
            $isCorrect = $answer && $answer->is_correct;
        ?>
        <span style="display:inline-block;width:30px;height:30px;text-align:center;line-height:30px;border:1px solid <?php echo e($isCorrect ? 'green' : 'red'); ?>;color:<?php echo e($isCorrect ? 'green' : 'red'); ?>">
            <?php echo e($i + 1); ?>

        </span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if($attempt->passed): ?>
    <p>Нараховано: <?php echo e($attempt->coins_awarded); ?> монет</p>
<?php else: ?>
    <p>Ви можете пройти тестування ще раз для підвищення балу.</p>
    <form method="POST" action="<?php echo e(route('tests.start', $test)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit">Перездати (-10 монет)</button>
    </form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/test/result.blade.php ENDPATH**/ ?>