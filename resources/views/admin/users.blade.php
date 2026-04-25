@extends('layouts.app')
@section('title', 'Управління користувачами')

@section('content')
<a href="{{ route('dashboard') }}">&larr; Дашборд</a>

<h1>Користувачі</h1>

@if(session('success'))
<p style="color:#27ae60;margin-bottom:10px;">{{ session('success') }}</p>
@endif
@if(session('error'))
<p style="color:#e74c3c;margin-bottom:10px;">{{ session('error') }}</p>
@endif

{{-- Search / Filter --}}
<form method="GET" action="{{ route('admin.users') }}">
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

<table>
    <thead>
        <tr>
            <th>ID</th><th>Ім'я</th><th>Прізвище</th><th>Телефон</th><th>Роль</th><th>VIP</th><th>Серія</th><th>Дії</th>
        </tr>
    </thead>
    <tbody>
    @foreach($users as $u)
        <tr>
            <td>{{ $u->id }}</td>
            <td>{{ $u->first_name }}</td>
            <td>{{ $u->last_name }}</td>
            <td>{{ $u->phone }}</td>
            <td>{{ $u->role }}</td>
            <td>{{ $u->isVip() ? '⭐' : '—' }}</td>
            <td>{{ $u->login_streak }}</td>
            <td>
                <form method="POST" action="{{ route('admin.users.role', $u) }}" style="display:inline;">
                    @csrf @method('PUT')
                    <select name="role">
                        @foreach(['superadmin','admin','teacher','student','parent','registered'] as $r)
                            <option value="{{ $r }}" @selected($u->role === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                    <button type="submit">Змінити</button>
                </form>

                @if($u->role === 'teacher')
                    <form method="POST" action="{{ route('superadmin.users.toggleTrusted', $u) }}" style="display:inline;">
                        @csrf
                        <button type="submit">{{ $u->is_trusted_teacher ? 'Зняти довіру' : 'Довірений' }}</button>
                    </form>
                @endif

                <a href="{{ route('profile.show', $u) }}">Профіль</a>

                @if(auth()->user()->isSuperAdmin() && $u->id !== auth()->id() && !$u->isSuperAdmin())
                <form method="POST" action="{{ route('superadmin.users.destroy', $u) }}" style="display:inline;"
                      onsubmit="return confirm('Видалити акаунт «{{ $u->full_name }}»?\n\nБудуть видалені всі дані: курси, транзакції, сповіщення тощо.\nЦю дію неможливо скасувати.')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:#e74c3c;color:#fff;border:none;padding:3px 9px;border-radius:4px;cursor:pointer;font-size:.8rem;">
                        Видалити
                    </button>
                </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $users->links() }}

<hr>

{{-- Parent-child linking --}}
<h2>Зв'язати батька і дитину</h2>
<form method="POST" action="{{ route('admin.users.linkParent') }}">
    @csrf
    <div>
        <label>ID батька</label>
        <input type="number" name="parent_id" required>
    </div>
    <div>
        <label>ID дитини (студента)</label>
        <input type="number" name="child_id" required>
    </div>
    <button type="submit">Зв'язати</button>
</form>
@endsection
