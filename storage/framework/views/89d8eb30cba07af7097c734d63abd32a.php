<?php $__env->startSection('title', 'Редагування профілю'); ?>

<?php $__env->startSection('content'); ?>
<h1>Редагування профілю</h1>

<form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

    <div>
        <label>Ім'я</label>
        <input type="text" name="first_name" value="<?php echo e(old('first_name', $user->first_name)); ?>" required>
    </div>

    <div>
        <label>Прізвище</label>
        <input type="text" name="last_name" value="<?php echo e(old('last_name', $user->last_name)); ?>" required>
    </div>

    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" required>
    </div>

    <div>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>">
    </div>

    <div>
        <label>Дата народження</label>
        <input type="date" name="birthday" value="<?php echo e(old('birthday', $user->birthday?->format('Y-m-d'))); ?>" required>
    </div>

    <div>
        <label>Про себе</label>
        <textarea name="bio" rows="4"><?php echo e(old('bio', $user->bio)); ?></textarea>
    </div>

    <div>
        <label>Аватар</label>
        <?php if($user->getFirstMediaUrl('avatar')): ?>
            <div>
                <img src="<?php echo e($user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" style="width:80px; height:80px; border-radius:50%;">
            </div>
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/*">
    </div>

    <button type="submit">Зберегти</button>
</form>


<?php if($user->isVip()): ?>
<hr>
<h2>Додаткові аватарки (VIP, до 5)</h2>
<?php $extraAvatars = $user->getMedia('extra_avatars'); ?>
<div>
    <?php $__currentLoopData = $extraAvatars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $avatar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <img src="<?php echo e($avatar->getUrl()); ?>" alt="Аватар" style="width:60px; height:60px; border-radius:50%; display:inline-block; margin:3px;">
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php if($extraAvatars->count() < 5): ?>
    <form method="POST" action="<?php echo e(route('profile.avatar.extra')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="file" name="avatar" accept="image/*" required>
        <button type="submit">Додати аватарку (<?php echo e($extraAvatars->count()); ?>/5)</button>
    </form>
<?php else: ?>
    <p>Максимум 5 аватарок.</p>
<?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/profile/edit.blade.php ENDPATH**/ ?>