<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Hashtag Space'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/sass/app.sass', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body>

<!-- ══ Top bar ══ -->
<header class="topbar">
    <div class="topbar-left">
        <?php if(auth()->guard()->check()): ?>
        <button class="burger" id="burger-btn" aria-label="Меню">
            <span></span><span></span><span></span>
        </button>
        <?php endif; ?>
        <a href="<?php echo e(route('home')); ?>" class="topbar-logo">Hashtag<span>#</span>Space</a>
    </div>

    <div class="topbar-right">
        <?php if(auth()->guard()->check()): ?>
        <?php $u = auth()->user(); ?>

        <?php if($u->hasRole(['student','teacher','admin','superadmin'])): ?>
        
        <a href="<?php echo e(route('wallet.index')); ?>" class="icon-btn" title="Гаманець">
            <span class="coin-icon">◈</span>
            <span class="coin-balance" id="wallet-balance"><?php echo e($u->wallet?->balance ?? 0); ?></span>
        </a>
        <?php endif; ?>

        
        <a href="<?php echo e(route('notifications.index')); ?>" class="icon-btn" title="Сповіщення">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="notif-badge" id="notif-badge"></span>
        </a>
        <?php endif; ?>
    </div>
</header>

<!-- ══ Nav overlay ══ -->
<div class="nav-overlay" id="nav-overlay"></div>

<!-- ══ Nav drawer ══ -->
<?php if(auth()->guard()->check()): ?>
<nav class="nav-drawer" id="nav-drawer">
    <?php $u = auth()->user(); ?>

    <?php if($u->isVip()): ?>
    <div class="nav-vip">⭐ VIP</div>
    <?php endif; ?>

    <a href="<?php echo e(route('dashboard')); ?>">Дашборд</a>

    <?php if($u->hasRole(['student','teacher','admin','superadmin'])): ?>
        <?php if($u->isTeacher() || $u->isAdmin()): ?>
            <a href="<?php echo e(route('teacher.courses.index')); ?>">Курси</a>
        <?php else: ?>
            <a href="<?php echo e(route('courses.public')); ?>">Курси</a>
        <?php endif; ?>
        <a href="<?php echo e(route('shop.index')); ?>">Магазин</a>
        <a href="<?php echo e(route('tests.index')); ?>">Тести</a>
        <a href="<?php echo e(route('schedule.index')); ?>">Розклад</a>
        <a href="<?php echo e(route('profile.edit')); ?>">Профіль</a>
    <?php endif; ?>

    <?php if($u->isAdmin()): ?>
        <hr class="nav-divider">
        <div class="nav-section">Адміністрація</div>
        <a href="<?php echo e(route('admin.users')); ?>">Користувачі</a>
        <a href="<?php echo e(route('admin.locations')); ?>">Локації та аудиторії</a>
        <a href="<?php echo e(route('admin.shop.index')); ?>">Управління магазином</a>
    <?php endif; ?>

    <?php if($u->isSuperAdmin()): ?>
        <a href="<?php echo e(route('superadmin.lesson.stats')); ?>">Заняття</a>
        <a href="<?php echo e(route('superadmin.transactions')); ?>">Всі транзакції</a>
    <?php endif; ?>

    <hr class="nav-divider">
    <form method="POST" action="<?php echo e(route('logout')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="nav-logout">Вийти</button>
    </form>
</nav>
<?php endif; ?>

<!-- ══ Alerts ══ -->
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

<footer></footer>

<script>
    window.csrfToken = '<?php echo e(csrf_token()); ?>';

    // Burger toggle
    const burger = document.getElementById('burger-btn');
    const drawer = document.getElementById('nav-drawer');
    const overlay = document.getElementById('nav-overlay');

    function toggleMenu(open) {
        burger?.classList.toggle('open', open);
        drawer?.classList.toggle('open', open);
        overlay?.classList.toggle('open', open);
    }

    burger?.addEventListener('click', () => toggleMenu(!drawer.classList.contains('open')));
    overlay?.addEventListener('click', () => toggleMenu(false));

    // Close drawer on nav link click (mobile UX)
    drawer?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => toggleMenu(false)));

    <?php if(auth()->guard()->check()): ?>
    // Poll unread notifications
    function updateNotifBadge(count) {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count;
            badge.classList.add('visible');
        } else {
            badge.textContent = '';
            badge.classList.remove('visible');
        }
    }

    setInterval(() => {
        fetch('<?php echo e(route("notifications.unreadCount")); ?>')
            .then(r => r.json())
            .then(d => updateNotifBadge(d.count));
    }, 30000);

    // Initial load
    fetch('<?php echo e(route("notifications.unreadCount")); ?>')
        .then(r => r.json())
        .then(d => updateNotifBadge(d.count));
    <?php endif; ?>
</script>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/layouts/app.blade.php ENDPATH**/ ?>