@extends('layouts.app')
@section('title', 'Заявки: ' . $course->title)

@section('content')
<a href="{{ route('teacher.courses.edit', $course) }}">&larr; Назад до курсу</a>

<h1>Заявки на курс: {{ $course->title }}</h1>

@if($applications->isEmpty())
    <p>Немає нових заявок.</p>
@else
    <table>
        <thead>
            <tr><th>Студент</th><th>Телефон</th><th>Дата заявки</th><th>Дії</th></tr>
        </thead>
        <tbody>
        @foreach($applications as $app)
            <tr>
                <td>{{ $app->user->last_name }} {{ $app->user->first_name }}</td>
                <td>{{ $app->user->phone }}</td>
                <td>{{ $app->created_at->format('d.m.Y H:i') }}</td>
                <td>
                    <form method="POST" action="{{ route('teacher.applications.approve', $app) }}" class="form-inline">
                        @csrf
                        <button type="submit">✅ Прийняти</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

<hr>

{{-- Add student directly --}}
<h2>Додати студента вручну</h2>
<form method="POST" action="{{ route('teacher.courses.addStudent', $course) }}">
    @csrf
    <div>
        <label>ID або телефон студента</label>
        <input type="text" name="user_id" placeholder="ID студента" required>
    </div>
    <button type="submit">Додати</button>
</form>
@endsection
