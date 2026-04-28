<?php $__env->startSection('title', 'Статистика тесту: ' . $test->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.edit', $test->course_id)); ?>">&larr; Назад до курсу</a>

<h1>Статистика: <?php echo e($test->title); ?></h1>
<p>Прохідний бал: <?php echo e($test->passing_score); ?>%</p>

<?php
    $grouped = $attempts->groupBy('user_id');
?>

<table>
    <thead>
        <tr>
            <th>Студент</th>
            <th>Спроба</th>
            <th>Бал</th>
            <th>Результат</th>
            <th>Підказки</th>
            <th>Дата</th>
            <th>Відповіді</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $grouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userId => $userAttempts): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $__currentLoopData = $userAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($attempt->user->last_name); ?> <?php echo e($attempt->user->first_name); ?></td>
            <td><?php echo e($attempt->attempt_number); ?></td>
            <td><?php echo e($attempt->score); ?>%</td>
            <td><?php echo e($attempt->passed ? 'Склав' : 'Не склав'); ?></td>
            <td><?php echo e($attempt->hints_used); ?></td>
            <td><?php echo e($attempt->completed_at ? $attempt->completed_at->format('d.m.Y H:i') : '—'); ?></td>
            <td>
                <details>
                    <summary>Показати відповіді</summary>
                    <ul>
                    <?php $__currentLoopData = $attempt->answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <strong><?php echo e($answer->question->text ?? '—'); ?></strong><br>
                            Відповідь: <?php echo e($answer->is_correct ? '✅ Правильно' : '❌ Неправильно'); ?>

                            <?php if($answer->hint_used): ?> (підказка) <?php endif; ?>
                            <br>
                            Вибрані: <?php echo e(implode(', ', collect($answer->selected_options)->map(fn($id) => \App\Models\TestOption::find($id)?->text ?? $id)->toArray())); ?>

                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </details>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php if($grouped->isEmpty()): ?>
    <p>Ще немає спроб.</p>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/test/statistics.blade.php ENDPATH**/ ?>