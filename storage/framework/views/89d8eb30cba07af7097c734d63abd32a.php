<?php $__env->startSection('title', 'Редагування профілю'); ?>

<?php $__env->startSection('content'); ?>
<h1>Редагування профілю</h1>

<?php if(session('success')): ?>
<p class="text-success mb-1"><?php echo e(session('success')); ?></p>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

    <div>
        <label>Ім'я</label>
        <input type="text" name="first_name" value="<?php echo e(old('first_name', $user->first_name)); ?>" required>
        <?php $__errorArgs = ['first_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Прізвище</label>
        <input type="text" name="last_name" value="<?php echo e(old('last_name', $user->last_name)); ?>" required>
        <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" required>
        <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Email</label>
        <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Дата народження</label>
        <?php if($user->birthday): ?>
            <input type="date" value="<?php echo e($user->birthday->format('Y-m-d')); ?>" disabled class="input-locked">
            <span class="text-xs text-muted">Дату народження неможливо змінити після встановлення.</span>
        <?php else: ?>
            <input type="date" name="birthday" value="<?php echo e(old('birthday')); ?>" required>
            <?php $__errorArgs = ['birthday'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php endif; ?>
    </div>

    <div>
        <label>Про себе</label>
        <textarea name="bio" rows="4"><?php echo e(old('bio', $user->bio)); ?></textarea>
        <?php $__errorArgs = ['bio'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Аватар</label>
        <?php if($user->getFirstMediaUrl('avatar')): ?>
            <div>
                <img src="<?php echo e($user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" class="avatar avatar-md">
            </div>
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/*">
        <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <button type="submit">Зберегти</button>
</form>

<?php if($user->isVip()): ?>
<hr>
<h2>Додаткові аватарки (VIP, до 5)</h2>
<?php $extraAvatars = $user->getMedia('extra_avatars'); ?>
<div>
    <?php $__currentLoopData = $extraAvatars; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $avatar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <img src="<?php echo e($avatar->getUrl()); ?>" alt="Аватар" class="avatar avatar-sm avatar-inline">
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

<hr>
<h2>Зміна пароля</h2>

<?php if(session('password_success')): ?>
<p class="text-success mb-1"><?php echo e(session('password_success')); ?></p>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('profile.password')); ?>">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

    <div>
        <label>Поточний пароль</label>
        <input type="password" name="current_password" required>
        <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Новий пароль</label>
        <input type="password" name="password" required minlength="8">
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="field-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div>
        <label>Повторіть новий пароль</label>
        <input type="password" name="password_confirmation" required>
    </div>

    <button type="submit">Змінити пароль</button>
</form>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/profile/edit.blade.php ENDPATH**/ ?>