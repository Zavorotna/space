<?php $__env->startSection('title', 'Тести'); ?>

<?php $__env->startSection('content'); ?>
<h1>Тести</h1>

<?php $user = auth()->user(); ?>

<?php if($user->isTeacher() || $user->isAdmin()): ?>
    <h2>Новий тест</h2>
    <form method="POST" action="" id="create-test-form">
        <?php echo csrf_field(); ?>
        <div>
            <label>Курс</label>
            <select name="course_id" required onchange="updateFormAction(this.value)">
                <option value="">— Оберіть курс —</option>
                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($course->id); ?>"><?php echo e($course->title); ?><?php echo e($course->is_template ? ' (шаблон)' : ''); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div><label>Назва тесту</label><input type="text" name="title" required></div>
        <div><label>Опис</label><textarea name="description"></textarea></div>
        <div><label>Прохідний бал (%)</label><input type="number" name="passing_score" value="60" min="1" max="100" required></div>
        <button type="submit">Створити тест</button>
    </form>
    <script>
    function updateFormAction(courseId) {
        document.getElementById('create-test-form').action = '/teacher/courses/' + courseId + '/tests';
    }
    </script>
    <hr>
<?php endif; ?>

<?php if($tests->isEmpty()): ?>
    <p>Тестів ще немає.</p>
<?php elseif($user->isTeacher() || $user->isAdmin()): ?>
    <?php $__currentLoopData = $tests->groupBy('course_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseId => $courseTests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $course = $courseTests->first()->course; ?>
        <h2><?php echo e($course->title); ?></h2>
        <table>
            <thead>
                <tr><th>Тест</th><th>Прохідний бал</th><th>Спроб</th><th>Дії</th></tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $courseTests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($test->title); ?></td>
                    <td><?php echo e($test->passing_score); ?>%</td>
                    <td><?php echo e($test->attempts->count()); ?></td>
                    <td>
                        <a href="<?php echo e(route('teacher.tests.edit', $test)); ?>">Редагувати</a>
                        <a href="<?php echo e(route('teacher.tests.statistics', $test)); ?>">Статистика</a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
    <?php $__currentLoopData = $tests->groupBy('course_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseId => $courseTests): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $course = $courseTests->first()->course; ?>
        <h2><?php echo e($course->title); ?></h2>
        <table>
            <thead>
                <tr><th>Тест</th><th>Прохідний бал</th><th>Мій результат</th><th>Дії</th></tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $courseTests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $bestAttempt = ($attempts[$test->id] ?? collect())->sortByDesc('score')->first(); ?>
                <tr>
                    <td><?php echo e($test->title); ?></td>
                    <td><?php echo e($test->passing_score); ?>%</td>
                    <td>
                        <?php if($bestAttempt): ?>
                            <?php echo e($bestAttempt->score); ?>% <?php echo e($bestAttempt->passed ? '✅' : '❌'); ?>

                        <?php else: ?> —
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?php echo e(route('tests.show', $test)); ?>"><?php echo e($bestAttempt ? 'Перездати' : 'Пройти'); ?></a>
                        <?php if($bestAttempt): ?>
                            <a href="<?php echo e(route('tests.result', $bestAttempt)); ?>">Результат</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/test/index.blade.php ENDPATH**/ ?>