<?php $__env->startSection('title', 'Локації та аудиторії'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('dashboard')); ?>">&larr; Дашборд</a>

<h1>Локації та аудиторії</h1>

<datalist id="cities-list">
    <?php $__currentLoopData = $cities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option value="<?php echo e($city); ?>">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</datalist>


<h2>Нова локація</h2>
<form method="POST" action="<?php echo e(route('admin.locations.store')); ?>">
    <?php echo csrf_field(); ?>
    <div><label>Назва</label><input type="text" name="name" required></div>
    <div>
        <label>Місто</label>
        <input type="text" name="city" id="new-city" list="cities-list"
               placeholder="Почніть вводити місто" autocomplete="off"
               onblur="addCityToList(this.value)">
    </div>
    <div><label>Вулиця / адреса</label><input type="text" name="street" placeholder="вул. Проскурівська 42"></div>
    <div>
        <label>Години роботи</label>
        <input type="time" name="work_start" value="09:00" required>
        —
        <input type="time" name="work_end" value="21:00" required>
    </div>
    <button type="submit">Створити</button>
</form>

<hr>


<?php $__empty_1 = true; $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div style="border:1px solid #ccc; padding:10px; margin:10px 0;">

    <form method="POST" action="<?php echo e(route('admin.locations.update', $location)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <div><label>Назва</label><input type="text" name="name" value="<?php echo e($location->name); ?>" required></div>
        <div>
            <label>Місто</label>
            <input type="text" name="city" list="cities-list"
                   value="<?php echo e($location->city); ?>" autocomplete="off"
                   onblur="addCityToList(this.value)">
        </div>
        <div><label>Вулиця / адреса</label><input type="text" name="street" value="<?php echo e($location->street); ?>"></div>
        <div>
            <label>Години роботи</label>
            <input type="time" name="work_start" value="<?php echo e(substr($location->work_start, 0, 5)); ?>" required>
            —
            <input type="time" name="work_end" value="<?php echo e(substr($location->work_end, 0, 5)); ?>" required>
        </div>
        <button type="submit">Зберегти</button>
    </form>

    <form method="POST" action="<?php echo e(route('admin.locations.destroy', $location)); ?>" style="display:inline; margin-top:6px;">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button type="submit" onclick="return confirm('Видалити локацію «<?php echo e($location->name); ?>»?')">Видалити локацію</button>
    </form>

    <h4>Аудиторії</h4>
    <?php $__currentLoopData = $location->classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="display:flex; gap:8px; align-items:center; margin-bottom:4px;">
        <form method="POST" action="<?php echo e(route('admin.classrooms.update', $room)); ?>" style="display:inline-flex; gap:6px;">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <input type="text" name="name" value="<?php echo e($room->name); ?>" required size="15">
            <input type="number" name="capacity" value="<?php echo e($room->capacity); ?>" min="1" size="5">
            <button type="submit">Зберегти</button>
        </form>
        <form method="POST" action="<?php echo e(route('admin.classrooms.destroy', $room)); ?>" style="display:inline;">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" onclick="return confirm('Видалити аудиторію «<?php echo e($room->name); ?>»?')">Видалити</button>
        </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($location->classrooms->isEmpty()): ?>
        <p>Ще немає аудиторій.</p>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('admin.classrooms.store', $location)); ?>" style="margin-top:8px;">
        <?php echo csrf_field(); ?>
        <input type="text" name="name" placeholder="Назва аудиторії" required>
        <input type="number" name="capacity" placeholder="Місткість" min="1">
        <button type="submit">Додати аудиторію</button>
    </form>

</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p>Ще немає локацій.</p>
<?php endif; ?>

<script>
const datalist = document.getElementById('cities-list');

function addCityToList(value) {
    value = value.trim();
    if (!value) return;

    const options = Array.from(datalist.options).map(o => o.value.toLowerCase());
    if (!options.includes(value.toLowerCase())) {
        const option = document.createElement('option');
        option.value = value;
        datalist.appendChild(option);
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/locations.blade.php ENDPATH**/ ?>