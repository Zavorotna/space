<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hashtag Space')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Top bar ── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #1a1a2e;
            color: #fff;
            padding: 0 16px;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-logo {
            font-weight: 700;
            font-size: 1.1rem;
            color: #fff;
            text-decoration: none;
            letter-spacing: .03em;
        }
        .topbar-logo span { color: #f5a623; }

        /* burger */
        .burger {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 4px;
        }
        .burger span {
            display: block;
            width: 22px;
            height: 2px;
            background: #fff;
            border-radius: 2px;
            transition: transform .25s, opacity .25s;
        }
        .burger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .burger.open span:nth-child(2) { opacity: 0; }
        .burger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ── Right icons (wallet + bell) ── */
        .topbar-right { display: flex; align-items: center; gap: 4px; }
        .icon-btn {
            position: relative;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #fff;
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.92rem;
            transition: background .2s;
        }
        .icon-btn:hover { background: rgba(255,255,255,.12); }
        .icon-btn svg { flex-shrink: 0; }

        .notif-badge {
            position: absolute;
            top: 2px;
            right: 4px;
            background: #e74c3c;
            color: #fff;
            border-radius: 99px;
            font-size: 0.68rem;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
        }
        .notif-badge.visible { display: flex; }

        .coin-icon { color: #f5a623; font-size: 1.1em; line-height: 1; }
        .coin-balance { font-weight: 600; font-size: 0.9rem; }

        /* ── Slide-out nav drawer ── */
        .nav-drawer {
            position: fixed;
            top: 56px;
            left: 0;
            width: 260px;
            height: calc(100vh - 56px);
            background: #1a1a2e;
            padding: 16px 0;
            transform: translateX(-100%);
            transition: transform .28s ease;
            z-index: 99;
            overflow-y: auto;
        }
        .nav-drawer.open { transform: translateX(0); }
        .nav-drawer a, .nav-drawer button.nav-logout {
            display: block;
            color: #d0d0e8;
            text-decoration: none;
            padding: 12px 24px;
            font-size: 0.97rem;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: background .18s, color .18s;
        }
        .nav-drawer a:hover, .nav-drawer button.nav-logout:hover {
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        .nav-drawer .nav-section {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #6c6c9a;
            padding: 14px 24px 4px;
        }
        .nav-drawer .nav-vip {
            padding: 6px 24px;
            color: #f5a623;
            font-size: 0.88rem;
        }
        .nav-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,.08);
            margin: 8px 0;
        }

        /* overlay */
        .nav-overlay {
            display: none;
            position: fixed;
            inset: 56px 0 0 0;
            background: rgba(0,0,0,.45);
            z-index: 98;
        }
        .nav-overlay.open { display: block; }

        /* main content */
        main { padding: 20px 16px; max-width: 960px; margin: 0 auto; }

        /* alerts */
        .alert-success, .alert-error, .alert-info {
            padding: 10px 16px;
            margin: 12px auto;
            max-width: 960px;
            border-radius: 6px;
            font-size: 0.93rem;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info    { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-error ul { margin: 0; padding-left: 18px; }

        footer { display: none; }
    </style>
    @stack('head')
</head>
<body>

<!-- ══ Top bar ══ -->
<header class="topbar">
    <div class="topbar-left">
        @auth
        <button class="burger" id="burger-btn" aria-label="Меню">
            <span></span><span></span><span></span>
        </button>
        @endauth
        <a href="{{ route('home') }}" class="topbar-logo">Hashtag<span>#</span>Space</a>
    </div>

    <div class="topbar-right">
        @auth
        @php $u = auth()->user(); @endphp

        @if($u->hasRole(['student','teacher','admin','superadmin']))
        {{-- Wallet --}}
        <a href="{{ route('wallet.index') }}" class="icon-btn" title="Гаманець">
            <span class="coin-icon">◈</span>
            <span class="coin-balance" id="wallet-balance">{{ $u->wallet?->balance ?? 0 }}</span>
        </a>
        @endif

        {{-- Notifications bell --}}
        <a href="{{ route('notifications.index') }}" class="icon-btn" title="Сповіщення">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span class="notif-badge" id="notif-badge"></span>
        </a>
        @endauth
    </div>
</header>

<!-- ══ Nav overlay ══ -->
<div class="nav-overlay" id="nav-overlay"></div>

<!-- ══ Nav drawer ══ -->
@auth
<nav class="nav-drawer" id="nav-drawer">
    @php $u = auth()->user(); @endphp

    @if($u->isVip())
    <div class="nav-vip">⭐ VIP</div>
    @endif

    <a href="{{ route('dashboard') }}">Дашборд</a>

    @if($u->hasRole(['student','teacher','admin','superadmin']))
        @if($u->isTeacher() || $u->isAdmin())
            <a href="{{ route('teacher.courses.index') }}">Курси</a>
        @else
            <a href="{{ route('courses.public') }}">Курси</a>
        @endif
        <a href="{{ route('shop.index') }}">Магазин</a>
        <a href="{{ route('tests.index') }}">Тести</a>
        <a href="{{ route('schedule.index') }}">Розклад</a>
        <a href="{{ route('profile.edit') }}">Профіль</a>
    @endif

    @if($u->isAdmin())
        <hr class="nav-divider">
        <div class="nav-section">Адміністрація</div>
        <a href="{{ route('admin.users') }}">Користувачі</a>
        <a href="{{ route('admin.locations') }}">Локації та аудиторії</a>
        <a href="{{ route('admin.shop.index') }}">Управління магазином</a>
    @endif

    @if($u->isSuperAdmin())
        <a href="{{ route('superadmin.lesson.stats') }}">Заняття</a>
        <a href="{{ route('superadmin.transactions') }}">Всі транзакції</a>
    @endif

    <hr class="nav-divider">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="nav-logout">Вийти</button>
    </form>
</nav>
@endauth

<!-- ══ Alerts ══ -->
@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif
@if(session('info'))
    <div class="alert-info">{{ session('info') }}</div>
@endif
@if($errors->any())
    <div class="alert-error">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<main>
    @yield('content')
</main>

<footer></footer>

<script>
    window.csrfToken = '{{ csrf_token() }}';

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

    @auth
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
        fetch('{{ route("notifications.unreadCount") }}')
            .then(r => r.json())
            .then(d => updateNotifBadge(d.count));
    }, 30000);

    // Initial load
    fetch('{{ route("notifications.unreadCount") }}')
        .then(r => r.json())
        .then(d => updateNotifBadge(d.count));
    @endauth
</script>

@stack('scripts')
</body>
</html>
