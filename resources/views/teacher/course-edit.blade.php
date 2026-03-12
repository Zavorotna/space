@extends('layouts.app')
@section('title', 'Редагування: ' . $course->title)
@section('content')
<h1>Редагування курсу: {{ $course->title }}</h1>

<form method="POST" action="{{ route('teacher.courses.update', $course) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div><label>Назва</label><input type="text" name="title" value="{{ $course->title }}" required></div>
    <div><label>Опис</label><textarea name="description">{{ $course->description }}</textarea></div>
    <div><label>Програма</label><textarea name="program">{{ $course->program }}</textarea></div>
    <div><label>Ціна</label><input type="number" name="price" step="0.01" value="{{ $course->price }}"></div>
    <div><label>Період</label>
        <select name="billing_period">
            <option value="monthly" @selected($course->billing_period==='monthly')>Щомісячно</option>
            <option value="one_time" @selected($course->billing_period==='one_time')>Разово</option>
        </select>
    </div>
    <div><label>Статус</label>
        <select name="status">
            @foreach(['waiting'=>'Очікування','enrolling'=>'Набір','active'=>'Активний','completed'=>'Завершений'] as $k=>$v)
                <option value="{{ $k }}" @selected($course->status===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div><label>Тип</label>
        <select name="type">
            <option value="group" @selected($course->type==='group')>Груповий</option>
            <option value="individual" @selected($course->type==='individual')>Індивідуальний</option>
        </select>
    </div>
    <div><label>Telegram</label><input type="url" name="telegram_link" value="{{ $course->telegram_link }}"></div>
    <div><label>Дата початку</label><input type="date" name="start_date" value="{{ $course->start_date?->format('Y-m-d') }}"></div>
    <div><label>Дата закінчення</label><input type="date" name="end_date" value="{{ $course->end_date?->format('Y-m-d') }}"></div>
    <div><label><input type="checkbox" name="is_published" value="1" @checked($course->is_published)> Опубліковано</label></div>
    <div><label>Фото</label><input type="file" name="cover" accept="image/*"></div>
    <button type="submit">Зберегти</button>
</form>

<form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}">
    @csrf
    <button type="submit">Скопіювати курс як шаблон</button>
</form>

<hr>
<h2>Студенти</h2>
<a href="{{ route('teacher.courses.students', $course) }}">Переглянути студентів</a>
<a href="{{ route('teacher.courses.applications', $course) }}">Заявки</a>

<h3>Додати студента напряму</h3>
<form method="POST" action="{{ route('teacher.courses.addStudent', $course) }}">
    @csrf
    <input type="number" name="user_id" placeholder="ID студента" required>
    <button type="submit">Додати</button>
</form>

<hr>
<h2>Домашні завдання</h2>
@foreach($course->homeworkAssignments as $hw)
<div>
    <strong>{{ $hw->title }}</strong> ({{ $hw->difficulty }}, {{ $hw->reward_coins }} монет)
    — до {{ $hw->deadline->format('d.m.Y') }}
    <a href="{{ route('teacher.homework.submissions', $hw) }}">Здачі ({{ $hw->submissions->count() }})</a>
</div>
@endforeach

<h3>Нове завдання</h3>
<form method="POST" action="{{ route('teacher.homework.store', $course) }}">
    @csrf
    <input type="text" name="title" placeholder="Назва" required>
    <textarea name="description" placeholder="Опис"></textarea>
    <select name="difficulty">
        <option value="easy">Легка (5 монет)</option>
        <option value="medium" selected>Середня (15 монет)</option>
        <option value="hard">Важка (25 монет)</option>
    </select>
    <input type="date" name="deadline" required>
    <button type="submit">Додати</button>
</form>

<hr>
<h2>Тести</h2>
@foreach($course->tests as $test)
<div>
    <strong>{{ $test->title }}</strong> ({{ $test->questions->count() }} питань)
    <a href="{{ route('teacher.tests.edit', $test) }}">Редагувати</a>
    <a href="{{ route('teacher.tests.statistics', $test) }}">Статистика</a>
</div>
@endforeach

<h3>Новий тест</h3>
<form method="POST" action="{{ route('teacher.tests.store', $course->id) }}">
    @csrf
    <input type="text" name="title" placeholder="Назва тесту" required>
    <textarea name="description" placeholder="Опис"></textarea>
    <input type="number" name="passing_score" value="60" min="1" max="100">% прохідний бал
    <button type="submit">Створити</button>
</form>

<hr>
<h2>Випускний проєкт</h2>
@if($course->graduationProject)
    <p>{{ $course->graduationProject->title }} — до {{ $course->graduationProject->deadline->format('d.m.Y') }}</p>
    <a href="{{ route('teacher.graduation.submissions', $course->graduationProject) }}">Здачі</a>
@else
    <form method="POST" action="{{ route('teacher.graduation.store', $course) }}">
        @csrf
        <input type="text" name="title" placeholder="Назва проєкту" required>
        <textarea name="description" placeholder="Опис"></textarea>
        <input type="date" name="deadline" required>
        <button type="submit">Створити</button>
    </form>
@endif

<hr>
<h2>Додаткові матеріали</h2>
@foreach($course->additionalMaterials as $mat)
<div>{{ $mat->title }} — {{ $mat->price_coins }} монет</div>
@endforeach
<form method="POST" action="{{ route('teacher.materials.store', $course) }}">
    @csrf
    <input type="text" name="title" placeholder="Назва" required>
    <textarea name="description" placeholder="Опис"></textarea>
    <input type="url" name="url" placeholder="Посилання">
    <input type="number" name="price_coins" value="0" min="0"> монет
    <button type="submit">Додати</button>
</form>
@endsection
