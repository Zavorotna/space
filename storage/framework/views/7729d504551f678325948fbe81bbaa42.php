<?php $__env->startSection('title', 'Створення курсу'); ?>
<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.index')); ?>">&larr; Курси</a>
<h1>Створення курсу</h1>
<form method="POST" action="<?php echo e(route('teacher.courses.store')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div><label><input type="checkbox" name="is_template" value="1" <?php if(old('is_template')): echo 'checked'; endif; ?>> Зберегти як шаблон</label></div>
    <div><label>Фото курсу</label><input type="file" name="cover" accept="image/*"></div>
    <div><label>Назва курсу</label><input type="text" name="title" value="<?php echo e(old('title')); ?>" required></div>
    <div><label>Опис курсу</label><textarea name="description"><?php echo e(old('description')); ?></textarea></div>
    <div><label>Програма</label><textarea name="program"><?php echo e(old('program')); ?></textarea></div>
    <div><label>Тип</label>
        <select name="type"><option value="group">Груповий</option><option value="individual">Індивідуальний</option></select>
    </div>
    <div><label>Ціна (грн)</label><input type="number" name="price" step="0.01" value="<?php echo e(old('price', 0)); ?>"></div>
    <div><label>Період оплати</label>
        <select name="billing_period"><option value="monthly">Щомісячно</option><option value="one_time">Разово</option><option value="per_lesson">За заняття</option></select>
    </div>
    <div><label>Telegram посилання</label><input type="url" name="telegram_link" value="<?php echo e(old('telegram_link')); ?>"></div>
    <div><label>Дата відкритого заняття</label><input type="date" name="intro_date" value="<?php echo e(old('intro_date')); ?>"></div>
    <div><label>Дата початку</label><input type="date" name="start_date" value="<?php echo e(old('start_date')); ?>"></div>
    <div><label>Дата закінчення</label><input type="date" name="end_date" value="<?php echo e(old('end_date')); ?>"></div>
    <div><label><input type="checkbox" name="has_graduation_project" value="1" checked> Є випускний проєкт</label></div>
    <button type="submit">Зберегти</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/course-create.blade.php ENDPATH**/ ?>