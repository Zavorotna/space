<?php $__env->startSection('title', 'Заявки: ' . $course->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.edit', $course)); ?>">&larr; Назад до курсу</a>

<h1>Заявки на курс: <?php echo e($course->title); ?></h1>

<?php if($applications->isEmpty()): ?>
    <p>Немає нових заявок.</p>
<?php else: ?>
    <table>
        <thead>
            <tr><th>Студент</th><th>Телефон</th><th>Дата заявки</th><th>Дії</th></tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($app->user->last_name); ?> <?php echo e($app->user->first_name); ?></td>
                <td><?php echo e($app->user->phone); ?></td>
                <td><?php echo e($app->created_at->format('d.m.Y H:i')); ?></td>
                <td>
                    <form method="POST" action="<?php echo e(route('teacher.applications.approve', $app)); ?>" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit">✅ Прийняти</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php endif; ?>

<hr>


<h2>Додати студента вручну</h2>
<form method="POST" action="<?php echo e(route('teacher.courses.addStudent', $course)); ?>">
    <?php echo csrf_field(); ?>
    <div>
        <label>ID або телефон студента</label>
        <input type="text" name="user_id" placeholder="ID студента" required>
    </div>
    <button type="submit">Додати</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/course-applications.blade.php ENDPATH**/ ?>