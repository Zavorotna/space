@extends('layouts.app')
@section('title', 'Управління користувачами')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Користувачі</h1>

@if(session('success'))
<p class="text-success mb-1">{{ session('success') }}</p>
@endif
@if(session('error'))
<p class="text-danger mb-1">{{ session('error') }}</p>
@endif
@if(session('notify_success'))
<p class="text-success mb-1">{{ session('notify_success') }}</p>
@endif

<form method="GET" action="{{ route('admin.users') }}" class="flex-row mb-2">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук за ім'ям, прізвищем, телефоном...">
    <select name="role">
        <option value="">— Всі ролі —</option>
        @foreach(['superadmin','admin','teacher','student','parent','registered'] as $r)
            <option value="{{ $r }}" @selected(request('role') === $r)>{{ $r }}</option>
        @endforeach
    </select>
    <button type="submit">Фільтрувати</button>
</form>

<hr>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th><th>Ім'я</th><th>Прізвище</th><th>Телефон</th><th>Роль</th><th>VIP</th><th>Серія</th><th>Дії</th>
        </tr>
    </thead>
    <tbody>
    @foreach($users as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td><a href="{{ route('profile.show', $u) }}" class="link-plain">{{ $u->first_name }}</a></td>
            <td><a href="{{ route('profile.show', $u) }}" class="link-plain">{{ $u->last_name }}</a></td>
            <td>{{ $u->phone }}</td>
            <td>{{ $u->role }}</td>
            <td>{{ $u->isVip() ? '⭐' : '—' }}</td>
            <td>{{ $u->login_streak }}</td>
            <td>
                <form method="POST" action="{{ route('admin.users.role', $u) }}" id="role-form-{{ $u->id }}">
                    @csrf @method('PUT')
                    <select name="role" onchange="document.getElementById('role-form-{{ $u->id }}').submit()">
                        @foreach(['superadmin','admin','teacher','student','parent','registered'] as $r)
                            <option value="{{ $r }}" @selected($u->role === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </form>

                @if($u->role === 'teacher')
                    <form method="POST" action="{{ route('superadmin.users.toggleTrusted', $u) }}" class="form-inline">
                        @csrf
                        <button type="submit">{{ $u->is_trusted_teacher ? 'Зняти довіру' : 'Довірений' }}</button>
                    </form>
                @endif

                @if(auth()->user()->id !== $u->id)
                <button type="button"
                        onclick="document.getElementById('msg-form-{{ $u->id }}').style.display = document.getElementById('msg-form-{{ $u->id }}').style.display === 'none' ? 'block' : 'none'"
                        class="btn btn-xs btn-primary">
                    Повідомлення
                </button>
                <div id="msg-form-{{ $u->id }}" class="msg-form" style="display:none;">
                    <form method="POST" action="{{ route('notifications.sendToUser', $u) }}">
                        @csrf
                        <textarea name="message" rows="2" required placeholder="Текст повідомлення..."
                                  class="msg-textarea"></textarea>
                        <button type="submit" class="btn btn-xs btn-primary mt-1">
                            Надіслати
                        </button>
                    </form>
                </div>
                @endif

                @if(auth()->user()->isSuperAdmin() && $u->id !== auth()->id() && !$u->isSuperAdmin())
                <form method="POST" action="{{ route('superadmin.users.destroy', $u) }}" class="form-inline"
                      onsubmit="return confirm('Видалити акаунт «{{ addslashes($u->full_name) }}»?\n\nБудуть видалені всі дані: курси, транзакції, сповіщення тощо.\nЦю дію неможливо скасувати.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-danger">Видалити</button>
                </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $users->links() }}

<hr>

<h2>Зв'язати батька і дитину</h2>
<form method="POST" action="{{ route('admin.users.linkParent') }}">
    @csrf
    <div><label>ID батька</label><input type="number" name="parent_id" required></div>
    <div><label>ID дитини (студента)</label><input type="number" name="child_id" required></div>
    <button type="submit">Зв'язати</button>
</form>
@endsection