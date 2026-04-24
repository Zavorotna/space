@extends('layouts.app')
@section('title', 'Курси')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Курси</h1>
    <div>
        <a href="{{ route('teacher.courses.create') }}">+ Новий курс</a>
    </div>
</div>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

{{-- ── Шаблони ─────────────────────────────────────────────── --}}
<h2>Шаблони</h2>
@if($templates->isEmpty())
    <p>Шаблонів ще немає. Створіть шаблон, щоб швидко запускати нові курси.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Назва</th>
                <th>Тип</th>
                <th>Ціна</th>
                @if(auth()->user()->isAdmin()) <th>Викладач</th> @endif
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
        @foreach($templates as $course)
            <tr>
                <td>{{ $course->title }}</td>
                <td>{{ $course->type === 'group' ? 'Груповий' : 'Індивідуальний' }}</td>
                <td>{{ $course->price }} грн</td>
                @if(auth()->user()->isAdmin())
                    <td>{{ $course->teacher?->full_name ?? '—' }}</td>
                @endif
                <td>
                    <a href="{{ route('teacher.courses.edit', $course) }}">Редагувати</a>
                    <form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}" style="display:inline;"
                          onsubmit="this.querySelector('button').disabled = true">
                        @csrf
                        <button type="submit">Копіювати як курс</button>
                    </form>
                    @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Видалити курс «{{ $course->title }}»?')">Видалити</button>
                    </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

{{-- ── Курси ────────────────────────────────────────────────── --}}
<h2>Мої курси</h2>
@if($courses->isEmpty())
    <p>Курсів ще немає.</p>
@else
    <table>
        <thead>
            <tr>
                <th>Назва</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Початок</th>
                <th>Кінець</th>
                @if(auth()->user()->isAdmin()) <th>Викладач</th> @endif
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
        @foreach($courses as $course)
            <tr>
                <td>{{ $course->title }}</td>
                <td>{{ $course->type === 'group' ? 'Груповий' : 'Індивідуальний' }}</td>
                <td>
                    @switch($course->status)
                        @case('waiting') Очікує @break
                        @case('enrolling') Набір @break
                        @case('active') Активний @break
                        @case('completed') Завершений @break
                    @endswitch
                    @if($course->is_published) ✅ @else ❌ @endif
                </td>
                <td>{{ $course->start_date?->format('d.m.Y') ?? '—' }}</td>
                <td>{{ $course->end_date?->format('d.m.Y') ?? '—' }}</td>
                @if(auth()->user()->isAdmin())
                    <td>{{ $course->teacher?->full_name ?? '—' }}</td>
                @endif
                <td>
                    <a href="{{ route('teacher.courses.edit', $course) }}">Редагувати</a>
                    <form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}" style="display:inline;"
                          onsubmit="this.querySelector('button').disabled = true">
                        @csrf
                        <button type="submit">Копіювати</button>
                    </form>
                    @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Видалити курс «{{ $course->title }}»?')">Видалити</button>
                    </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
@endsection