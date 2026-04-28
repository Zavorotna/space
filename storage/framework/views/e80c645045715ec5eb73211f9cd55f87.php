<?php $__env->startSection('title', 'Вхід'); ?>
<?php $__env->startSection('content'); ?>
<h1>Вхід</h1>

<?php if($errors->has('session')): ?>
<div class="alert-box alert-box--warn">
    <?php echo e($errors->first('session')); ?>

</div>
<?php endif; ?>

<?php if($errors->any() && !$errors->has('session')): ?>
<div class="alert-box alert-box--error">
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div><?php echo e($error); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<form method="POST" action="<?php echo e(route('login')); ?>" id="login-form">
    <?php echo csrf_field(); ?>
    <div>
        <label>Номер телефону</label>
        <input type="text" name="phone" value="<?php echo e(old('phone')); ?>" required autocomplete="username">
    </div>
    <div>
        <label>Пароль</label>
        <input type="password" name="password" required autocomplete="current-password">
    </div>
    <div>
        <label><input type="checkbox" name="remember"> Запам'ятати мене</label>
    </div>
    <button type="submit">Увійти</button>
</form>

<hr>

<a href="<?php echo e(route('auth.google')); ?>" class="btn-google">Увійти через Google</a>

<p>Немає акаунту? <a href="<?php echo e(route('register')); ?>">Зареєструватися</a></p>

<script>
(function () {
    function refreshCsrf() {
        fetch('/csrf-token')
            .then(r => r.json())
            .then(data => {
                document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.token);
                window.csrfToken = data.token;
            })
            .catch(() => {});
    }

    setInterval(refreshCsrf, 4 * 60 * 1000);

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') refreshCsrf();
    });

    window.addEventListener('focus', refreshCsrf);
})();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/auth/login.blade.php ENDPATH**/ ?>