@extends('layouts.app')
@section('title', 'Статистика тесту: ' . $test->title)

@section('content')
<a href="{{ route('teacher.courses.edit', $test->course_id) }}">&larr; Назад до курсу</a>

<h1>Статистика: {{ $test->title }}</h1>
<p>Прохідний бал: {{ $test->passing_score }}%</p>

@php
    $grouped = $attempts->groupBy('user_id');
@endphp

<table>
    <thead>
        <tr>
            <th>Студент</th>
            <th>Спроба</th>
            <th>Бал</th>
            <th>Результат</th>
            <th>Підказки</th>
            <th>Дата</th>
            <th>Відповіді</th>
        </tr>
    </thead>
    <tbody>
    @foreach($grouped as $userId => $userAttempts)
        @foreach($userAttempts as $attempt)
        <tr>
            <td>{{ $attempt->user->last_name }} {{ $attempt->user->first_name }}</td>
            <td>{{ $attempt->attempt_number }}</td>
            <td>{{ $attempt->score }}%</td>
            <td>{{ $attempt->passed ? 'Склав' : 'Не склав' }}</td>
            <td>{{ $attempt->hints_used }}</td>
            <td>{{ $attempt->completed_at ? $attempt->completed_at->format('d.m.Y H:i') : '—' }}</td>
            <td>
                <details>
                    <summary>Показати відповіді</summary>
                    <ul>
                    @foreach($attempt->answers as $answer)
                        <li>
                            <strong>{{ $answer->question->text ?? '—' }}</strong><br>
                            Відповідь: {{ $answer->is_correct ? '✅ Правильно' : '❌ Неправильно' }}
                            @if($answer->hint_used) (підказка) @endif
                            <br>
                            Вибрані: {{ implode(', ', collect($answer->selected_options)->map(fn($id) => \App\Models\TestOption::find($id)?->text ?? $id)->toArray()) }}
                        </li>
                    @endforeach
                    </ul>
                </details>
            </td>
        </tr>
        @endforeach
    @endforeach
    </tbody>
</table>

@if($grouped->isEmpty())
    <p>Ще немає спроб.</p>
@endif
@endsection
