<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hashtag Space')</title>
    @stack('head')
</head>
<body>
    <nav>
        <a href="{{ route('home') }}">Hashtag Space</a>
        @auth
            @php $u = auth()->user(); @endphp
            <a href="{{ route('dashboard') }}">Головна</a>
            <a href="{{ route('courses.public') }}">Курси</a>
            <a href="{{ route('shop.index') }}">Магазин</a>

            @if($u->hasRole(['student','teacher','admin','superadmin']))
                <a href="{{ route('schedule.index') }}">Розклад</a>
                <a href="{{ route('wallet.index') }}">
                    Гаманець
                    @if($u->wallet)
                        ({{ $u->wallet->balance }})
                    @endif
                </a>
            @endif

            @if($u->isVip())
                <span>⭐ VIP</span>
            @endif

            <a href="{{ route('notifications.index') }}">
                Сповіщення
                <span id="notif-badge"></span>
            </a>

            <a href="{{ route('profile.edit') }}">Профіль</a>

            @if($u->isAdmin())
                <a href="{{ route('admin.users') }}">Адмін</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Вийти</button>
            </form>
        @else
            <a href="{{ route('login') }}">Увійти</a>
            <a href="{{ route('register') }}">Зареєструватися</a>
        @endauth
    </nav>

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
        <nav>
            <a href="{{ route('shop.index') }}">Магазин</a>
            <a href="{{ route('courses.public') }}">Курси</a>
            <a href="{{ route('home') }}">Головна</a>
            @auth
                <a href="{{ route('profile.edit') }}">Профіль</a>
            @endauth
        </nav>
    </footer>

    <script>
        // CSRF token for AJAX
        window.csrfToken = '{{ csrf_token() }}';

        // Poll unread notifications
        @auth
        setInterval(() => {
            fetch('{{ route("notifications.unreadCount") }}')
                .then(r => r.json())
                .then(d => {
                    const badge = document.getElementById('notif-badge');
                    if (badge) badge.textContent = d.count > 0 ? d.count : '';
                });
        }, 30000);
        @endauth
    </script>

    @stack('scripts')
</body>
</html>
