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
            <option value="per_lesson" @selected($course->billing_period==='per_lesson')>За заняття</option>
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
    @if(auth()->user()->isAdmin())
    <div>
        <label>Викладач</label>
        <select name="teacher_id">
            <option value="">— не призначено —</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected($course->teacher_id === $teacher->id)>
                    {{ $teacher->last_name }} {{ $teacher->first_name }} ({{ $teacher->role }})
                </option>
            @endforeach
        </select>
    </div>
    @endif
    <div><label>Telegram</label><input type="url" name="telegram_link" value="{{ $course->telegram_link }}"></div>
    <div><label>Дата початку</label><input type="date" name="start_date" value="{{ $course->start_date?->format('Y-m-d') }}"></div>
    <div><label>Дата закінчення</label><input type="date" name="end_date" value="{{ $course->end_date?->format('Y-m-d') }}"></div>
    <div><label><input type="checkbox" name="is_published" value="1" @checked($course->is_published)> Опубліковано</label></div>
    <div>
        <label>Фото</label>
        @if($course->getFirstMediaUrl('cover'))
            <div><img src="{{ $course->getFirstMediaUrl('cover') }}" alt="Обкладинка" style="max-width:200px; display:block; margin-bottom:6px;"></div>
        @endif
        <input type="file" name="cover" accept="image/*">
    </div>
    <button type="submit">Зберегти</button>
</form>

<form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}"
      onsubmit="this.querySelector('button').disabled = true">
    @csrf
    <button type="submit">Скопіювати курс як шаблон</button>
</form>

@if(auth()->user()->isSuperAdmin())
<form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" id="delete-course-form">
    @csrf @method('DELETE')
    <button type="button" onclick="showDeleteConfirm()">Видалити курс</button>
</form>

<div id="delete-confirm" style="display:none; border:1px solid red; padding:15px; margin-top:10px;">
    <p><strong>Ви впевнені, що хочете видалити курс «{{ $course->title }}»?</strong></p>
    <p>Ця дія незворотна. Введіть назву курсу для підтвердження:</p>
    <input type="text" id="confirm-title" placeholder="{{ $course->title }}">
    <br><br>
    <button type="button" id="confirm-delete-btn" disabled onclick="document.getElementById('delete-course-form').submit()">Так, видалити</button>
    <button type="button" onclick="hideDeleteConfirm()">Скасувати</button>
</div>

<script>
function showDeleteConfirm() {
    document.getElementById('delete-confirm').style.display = 'block';
}
function hideDeleteConfirm() {
    document.getElementById('delete-confirm').style.display = 'none';
    document.getElementById('confirm-title').value = '';
    document.getElementById('confirm-delete-btn').disabled = true;
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('confirm-title').addEventListener('input', function () {
        document.getElementById('confirm-delete-btn').disabled = this.value !== '{{ addslashes($course->title) }}';
    });
});
</script>
@endif

<hr>
<h2>Співвикладачі</h2>
@if($course->coTeachers->count())
    @foreach($course->coTeachers as $coTeacher)
    <div>
        {{ $coTeacher->last_name }} {{ $coTeacher->first_name }} ({{ $coTeacher->role }})
        @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('teacher.courses.coTeachers.remove', [$course, $coTeacher]) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('Видалити співвикладача?')">Видалити</button>
        </form>
        @endif
    </div>
    @endforeach
@else
    <p>Співвикладачів ще немає.</p>
@endif

@if(auth()->user()->isAdmin())
<form method="POST" action="{{ route('teacher.courses.coTeachers.add', $course) }}">
    @csrf
    <select name="user_id" required>
        <option value="">— Оберіть викладача —</option>
        @foreach($teachers->filter(fn($t) => $t->id !== $course->teacher_id && !$course->coTeachers->contains($t->id)) as $teacher)
            <option value="{{ $teacher->id }}">{{ $teacher->last_name }} {{ $teacher->first_name }} ({{ $teacher->role }})</option>
        @endforeach
    </select>
    <button type="submit">Додати співвикладача</button>
</form>
@endif

<hr>
<h2>Студенти ({{ $course->students->count() }})</h2>
<a href="{{ route('teacher.courses.applications', $course) }}">Заявки</a>

@if($course->students->count())
<div style="max-height: {{ $course->students->count() > 10 ? '400px' : 'none' }}; overflow-y: {{ $course->students->count() > 10 ? 'auto' : 'visible' }}; border: 1px solid #ddd; margin: 10px 0;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Студент</th>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Статус</th>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Оплата</th>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Записаний</th>
            </tr>
        </thead>
        <tbody>
        @foreach($course->students as $student)
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:6px 8px;"><a href="{{ route('profile.show', $student) }}">{{ $student->last_name }} {{ $student->first_name }}</a></td>
                <td style="padding:6px 8px;">
                    @switch($student->pivot->status)
                        @case('active') Активний @break
                        @case('completed') Завершив @break
                        @case('pending') Очікує @break
                        @default {{ $student->pivot->status }}
                    @endswitch
                </td>
                <td style="padding:6px 8px;">{{ $student->pivot->is_paid ? '✅' : '❌' }}</td>
                <td style="padding:6px 8px;">{{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d.m.Y') : '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@else
    <p>Студентів ще немає.</p>
@endif

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
