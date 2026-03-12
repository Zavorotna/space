<?php $__env->startSection('title', 'Реєстрація'); ?>
<?php $__env->startSection('content'); ?>
<h1>Реєстрація</h1>
<form method="POST" action="<?php echo e(route('register')); ?>">
    <?php echo csrf_field(); ?>
    <div><label>Ім'я</label><input type="text" name="first_name" value="<?php echo e(old('first_name')); ?>" required></div>
    <div><label>Прізвище</label><input type="text" name="last_name" value="<?php echo e(old('last_name')); ?>" required></div>
    <div><label>Номер телефону</label><input type="text" name="phone" value="<?php echo e(old('phone')); ?>" required></div>
    <div><label>Дата народження</label><input type="date" name="birthday" value="<?php echo e(old('birthday')); ?>" required></div>
    <div><label>Пароль</label><input type="password" name="password" required></div>
    <div><label>Підтвердження паролю</label><input type="password" name="password_confirmation" required></div>
    <button type="submit">Зареєструватися</button>
</form>
<p>зареєструватися через</p>
<a href="<?php echo e(route('auth.google')); ?>">Google</a>
<p>Маєш акаунт? <a href="<?php echo e(route('login')); ?>">Увійти</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/auth/register.blade.php ENDPATH**/ ?>