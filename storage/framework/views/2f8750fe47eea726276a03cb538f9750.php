<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Hashtag Space'); ?></title>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body>
    <nav>
        <a href="<?php echo e(route('home')); ?>">Hashtag Space</a>
        <?php if(auth()->guard()->check()): ?>
            <?php $u = auth()->user(); ?>
            <a href="<?php echo e(route('dashboard')); ?>">Головна</a>
            <a href="<?php echo e(route('courses.public')); ?>">Курси</a>
            <a href="<?php echo e(route('shop.index')); ?>">Магазин</a>

            <?php if($u->hasRole(['student','teacher','admin','superadmin'])): ?>
                <a href="<?php echo e(route('schedule.index')); ?>">Розклад</a>
                <a href="<?php echo e(route('wallet.index')); ?>">
                    Гаманець
                    <?php if($u->wallet): ?>
                        (<?php echo e($u->wallet->balance); ?>)
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if($u->isVip()): ?>
                <span>⭐ VIP</span>
            <?php endif; ?>

            <a href="<?php echo e(route('notifications.index')); ?>">
                Сповіщення
                <span id="notif-badge"></span>
            </a>

            <a href="<?php echo e(route('profile.edit')); ?>">Профіль</a>

            <?php if($u->isAdmin()): ?>
                <a href="<?php echo e(route('admin.users')); ?>">Адмін</a>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline">
                <?php echo csrf_field(); ?>
                <button type="submit">Вийти</button>
            </form>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>">Увійти</a>
            <a href="<?php echo e(route('register')); ?>">Зареєструватися</a>
        <?php endif; ?>
    </nav>

    <?php if(session('success')): ?>
        <div class="alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert-error"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    <?php if(session('info')): ?>
        <div class="alert-info"><?php echo e(session('info')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert-error">
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer>
        <nav>
            <a href="<?php echo e(route('shop.index')); ?>">Магазин</a>
            <a href="<?php echo e(route('courses.public')); ?>">Курси</a>
            <a href="<?php echo e(route('home')); ?>">Головна</a>
            <?php if(auth()->guard()->check()): ?>
                <a href="<?php echo e(route('profile.edit')); ?>">Профіль</a>
            <?php endif; ?>
        </nav>
    </footer>

    <script>
        // CSRF token for AJAX
        window.csrfToken = '<?php echo e(csrf_token()); ?>';

        // Poll unread notifications
        <?php if(auth()->guard()->check()): ?>
        setInterval(() => {
            fetch('<?php echo e(route("notifications.unreadCount")); ?>')
                .then(r => r.json())
                .then(d => {
                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.textContent = d.count > 0 ? d.count : '';
                });
        }, 30000);
        <?php endif; ?>
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/user/Documents/project/space/resources/views/layouts/app.blade.php ENDPATH**/ ?>