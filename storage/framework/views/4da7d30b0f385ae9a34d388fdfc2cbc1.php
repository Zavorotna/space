<?php $__env->startSection('title', 'Студенти: ' . $course->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.edit', $course)); ?>">&larr; Назад до курсу</a>

<h1>Студенти курсу: <?php echo e($course->title); ?></h1>

<?php if($students->isEmpty()): ?>
    <p>Немає студентів на курсі.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Ім'я</th>
            <th>Прізвище</th>
            <th>Телефон</th>
            <th>Оплата</th>
            <th>Успішність</th>
            <th>Баланс</th>
            <th>Дата завершення</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($student->first_name); ?></td>
            <td><?php echo e($student->last_name); ?></td>
            <td><?php echo e($student->phone); ?></td>
            <td><?php echo e($student->pivot->is_paid ? '✅' : '❌'); ?></td>
            <td><?php echo e($student->pivot->success_rate ?? 0); ?>%</td>
            <td><?php echo e($student->wallet?->balance ?? 0); ?></td>
            <td><?php echo e($student->active_until ? \Carbon\Carbon::parse($student->active_until)->format('d.m.Y') : '—'); ?></td>
            <td>
                <a href="<?php echo e(route('profile.show', $student)); ?>">Профіль</a>
                
                <form method="POST" action="<?php echo e(route('teacher.certificates.issue', [$course, $student->id])); ?>" style="display:inline;" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="certificate_image" accept="image/*">
                    <button type="submit">Видати сертифікат</button>
                </form>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php endif; ?>

<hr>


<h2>Змінити дату завершення курсу</h2>
<p>Поточна: <?php echo e($course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('d.m.Y') : 'не встановлена'); ?></p>
<form method="POST" action="<?php echo e(route('teacher.courses.endDate', $course)); ?>">
    <?php echo csrf_field(); ?>
    <input type="date" name="end_date" value="<?php echo e($course->end_date?->format('Y-m-d') ?? ''); ?>" required>
    <button type="submit">Змінити</button>
</form>


<?php if(auth()->user()->role === 'superadmin'): ?>
<hr>
<h2>LiqPay налаштування (ФОП)</h2>
<form method="POST" action="<?php echo e(route('superadmin.courses.liqpay', $course)); ?>">
    <?php echo csrf_field(); ?>
    <div>
        <label>Merchant ID</label>
        <input type="text" name="liqpay_merchant_id" value="<?php echo e($course->liqpay_merchant_id); ?>">
    </div>
    <div>
        <label>Private Key</label>
        <input type="text" name="liqpay_private_key" value="<?php echo e($course->liqpay_private_key); ?>">
    </div>
    <button type="submit">Зберегти</button>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/course-students.blade.php ENDPATH**/ ?>