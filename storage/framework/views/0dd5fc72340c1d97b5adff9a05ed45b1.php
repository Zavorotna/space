<?php $__env->startSection('title', 'Вхід'); ?>
<?php $__env->startSection('content'); ?>
<h1>Вхід</h1>
<form method="POST" action="<?php echo e(route('login')); ?>">
    <?php echo csrf_field(); ?>
    <div><label>Номер телефону</label><input type="text" name="phone" value="<?php echo e(old('phone')); ?>" required></div>
    <div><label>Пароль</label><input type="password" name="password" required></div>
    <div><label><input type="checkbox" name="remember"> Запам'ятати мене</label></div>
    <button type="submit">Увійти</button>
</form>
<a href="<?php echo e(route('auth.google')); ?>">Увійти через Google</a>
<p>Немає акаунту? <a href="<?php echo e(route('register')); ?>">Зареєструватися</a></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/auth/login.blade.php ENDPATH**/ ?>