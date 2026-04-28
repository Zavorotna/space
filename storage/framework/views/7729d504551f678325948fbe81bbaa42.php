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

    <hr>
    <h3>Розклад занять (для автогенерації)</h3>
    <p class="text-sm text-muted">Якщо заповнено — заняття будуть автоматично додані до розкладу при призначенні викладача.</p>

    <div>
        <label>Дні тижня</label><br>
        <?php $__currentLoopData = [1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Нд']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label class="schedule-day-label">
            <input type="checkbox" name="schedule_days[]" value="<?php echo e($num); ?>"
                   <?php if(in_array($num, old('schedule_days', []))): echo 'checked'; endif; ?>>
            <?php echo e($label); ?>

        </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="schedule-time-row">
        <div><label>Початок заняття</label><br><input type="time" name="schedule_start_time" value="<?php echo e(old('schedule_start_time')); ?>"></div>
        <div><label>Кінець заняття</label><br><input type="time" name="schedule_end_time" value="<?php echo e(old('schedule_end_time')); ?>"></div>
        <div>
            <label>Формат</label><br>
            <select name="schedule_mode" id="sched-mode-create" onchange="toggleSchedLocation('create',this.value)">
                <option value="online" <?php if(old('schedule_mode','online')==='online'): echo 'selected'; endif; ?>>Онлайн</option>
                <option value="offline" <?php if(old('schedule_mode')==='offline'): echo 'selected'; endif; ?>>Офлайн</option>
            </select>
        </div>
    </div>
    <div id="sched-loc-create" class="schedule-loc-block" style="display:<?php echo e(old('schedule_mode')==='offline'?'block':'none'); ?>;">
        <div>
            <label>Локація</label><br>
            <select name="schedule_location_id" id="sched-loc-sel-create" onchange="filterClassrooms('create',this.value)">
                <option value="">— Оберіть —</option>
                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($loc->id); ?>" <?php if(old('schedule_location_id')==$loc->id): echo 'selected'; endif; ?>><?php echo e($loc->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="mt-1">
            <label>Аудиторія</label><br>
            <select name="schedule_classroom_id" id="sched-room-sel-create">
                <option value="">— Оберіть —</option>
                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $loc->classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($room->id); ?>" data-location="<?php echo e($loc->id); ?>" <?php if(old('schedule_classroom_id')==$room->id): echo 'selected'; endif; ?>>
                        <?php echo e($loc->name); ?> — <?php echo e($room->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>

    <button type="submit" class="btn mt-2">Зберегти</button>
</form>

<script>
function toggleSchedLocation(suffix, val) {
    document.getElementById('sched-loc-' + suffix).style.display = val === 'offline' ? 'block' : 'none';
}
function filterClassrooms(suffix, locationId) {
    const sel = document.getElementById('sched-room-sel-' + suffix);
    Array.from(sel.options).forEach(o => {
        o.style.display = (!o.dataset.location || o.dataset.location == locationId || !locationId) ? '' : 'none';
    });
    sel.value = '';
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/course-create.blade.php ENDPATH**/ ?>