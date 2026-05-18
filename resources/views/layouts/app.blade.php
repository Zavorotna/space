<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hashtag Space')</title>
    @vite(['resources/sass/app.sass', 'resources/js/app.js'])
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

        {{-- Wallet (all authenticated users) --}}
        <a href="{{ route('wallet.index') }}" class="icon-btn" title="Гаманець">
            <span class="coin-icon">◈</span>
            <span class="coin-balance" id="wallet-balance">{{ $u->wallet?->balance ?? 0 }}</span>
        </a>

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
    @elseif($u->isRegistered())
        <a href="{{ route('courses.public') }}">Курси</a>
        <a href="{{ route('shop.index') }}">Магазин</a>
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

<footer>
    <div class="footer-legal">
        <a href="{{ route('legal') }}">Публічна оферта</a>
        <span>·</span>
        <a href="{{ route('legal') }}?tab=privacy">Політика конфіденційності</a>
        <span>·</span>
        <a href="{{ route('legal') }}?tab=refund">Умови повернення</a>
    </div>
</footer>

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
