@extends('layouts.app')
@section('title', 'Тести')

@section('content')
<h1>Тести</h1>

@php $user = auth()->user(); @endphp

@if($user->isTeacher() || $user->isAdmin())
    <h2>Новий тест</h2>
    <form method="POST" action="" id="create-test-form">
        @csrf
        <div>
            <label>Курс</label>
            <select name="course_id" required onchange="updateFormAction(this.value)">
                <option value="">— Оберіть курс —</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->title }}{{ $course->is_template ? ' (шаблон)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div><label>Назва тесту</label><input type="text" name="title" required></div>
        <div><label>Опис</label><textarea name="description"></textarea></div>
        <div><label>Прохідний бал (%)</label><input type="number" name="passing_score" value="60" min="1" max="100" required></div>
        <button type="submit">Створити тест</button>
    </form>
    <script>
    function updateFormAction(courseId) {
        document.getElementById('create-test-form').action = '/teacher/courses/' + courseId + '/tests';
    }
    </script>
    <hr>
@endif

@if($tests->isEmpty())
    <p>Тестів ще немає.</p>
@elseif($user->isTeacher() || $user->isAdmin())
    @foreach($tests->groupBy('course_id') as $courseId => $courseTests)
        @php $course = $courseTests->first()->course; @endphp
        <h2>{{ $course->title }}</h2>
        <table>
            <thead>
                <tr><th>Тест</th><th>Прохідний бал</th><th>Спроб</th><th>Дії</th></tr>
            </thead>
            <tbody>
            @foreach($courseTests as $test)
                <tr>
                    <td>{{ $test->title }}</td>
                    <td>{{ $test->passing_score }}%</td>
                    <td>{{ $test->attempts_count }}</td>
                    <td>
                        <a href="{{ route('tests.edit', $test) }}">Редагувати</a>
                        <a href="{{ route('tests.statistics', $test) }}">Статистика</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
@else
    @foreach($tests->groupBy('course_id') as $courseId => $courseTests)
        @php $course = $courseTests->first()->course; @endphp
        <h2>{{ $course->title }}</h2>
        <table>
            <thead>
                <tr><th>Тест</th><th>Прохідний бал</th><th>Мій результат</th><th>Дії</th></tr>
            </thead>
            <tbody>
            @foreach($courseTests as $test)
                @php $bestAttempt = ($attempts[$test->id] ?? collect())->sortByDesc('score')->first(); @endphp
                <tr>
                    <td>{{ $test->title }}</td>
                    <td>{{ $test->passing_score }}%</td>
                    <td>
                        @if($bestAttempt)
                            {{ $bestAttempt->score }}% {{ $bestAttempt->passed ? '✅' : '❌' }}
                        @else —
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('tests.show', $test) }}">{{ $bestAttempt ? 'Перездати' : 'Пройти' }}</a>
                        @if($bestAttempt)
                            <a href="{{ route('tests.result', $bestAttempt) }}">Результат</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endforeach
@endif
@endsection