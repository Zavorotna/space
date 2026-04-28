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
            <div><img src="{{ $course->getFirstMediaUrl('cover') }}" alt="Обкладинка" class="course-cover"></div>
        @endif
        <input type="file" name="cover" accept="image/*">
    </div>

    <hr>
    <h3>Розклад занять</h3>
    <p class="text-sm text-muted">Змінення розкладу тут не перегенеровує вже існуючі заняття. Щоб додати нові — натисніть «Згенерувати заняття» після збереження.</p>
    <div>
        <label>Дні тижня</label><br>
        @foreach([1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Нд'] as $num => $label)
        <label class="schedule-day-label">
            <input type="checkbox" name="schedule_days[]" value="{{ $num }}"
                   @checked(is_array($course->schedule_days) && in_array($num, $course->schedule_days))>
            {{ $label }}
        </label>
        @endforeach
    </div>
    <div class="schedule-time-row">
        <div><label>Початок заняття</label><br>
            <input type="time" name="schedule_start_time" value="{{ $course->schedule_start_time ? substr($course->schedule_start_time,0,5) : '' }}"></div>
        <div><label>Кінець заняття</label><br>
            <input type="time" name="schedule_end_time" value="{{ $course->schedule_end_time ? substr($course->schedule_end_time,0,5) : '' }}"></div>
        <div>
            <label>Формат</label><br>
            <select name="schedule_mode" id="sched-mode-edit" onchange="toggleSchedLocation('edit',this.value)">
                <option value="online" @selected(($course->schedule_mode ?? 'online')==='online')>Онлайн</option>
                <option value="offline" @selected($course->schedule_mode==='offline')>Офлайн</option>
            </select>
        </div>
    </div>
    <div id="sched-loc-edit" class="schedule-loc-block" style="display:{{ $course->schedule_mode==='offline'?'block':'none' }};">
        <div>
            <label>Локація</label><br>
            <select name="schedule_location_id" id="sched-loc-sel-edit" onchange="filterClassrooms('edit',this.value)">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected($course->schedule_location_id == $loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-1">
            <label>Аудиторія</label><br>
            <select name="schedule_classroom_id" id="sched-room-sel-edit">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                    @foreach($loc->classrooms as $room)
                    <option value="{{ $room->id }}" data-location="{{ $loc->id }}"
                            @selected($course->schedule_classroom_id == $room->id)>
                        {{ $loc->name }} — {{ $room->name }}
                    </option>
                    @endforeach
                @endforeach
            </select>
        </div>
    </div>

    <button type="submit" class="btn mt-2">Зберегти</button>
</form>


<script>
function toggleSchedLocation(suffix, val) {
    document.getElementById('sched-loc-' + suffix).style.display = val === 'offline' ? 'block' : 'none';
}
function filterClassrooms(suffix, locationId) {
    const sel = document.getElementById('sched-room-sel-' + suffix);
    Array.from(sel.options).forEach(o => {
        o.style.display = (!o.dataset.location || o.dataset.location == locationId || !locationId) ? '' : 'none';
    });
    sel.value = '';
}
</script>

<form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}"
      onsubmit="this.querySelector('button').disabled = true">
    @csrf
    <button type="submit">Скопіювати курс як шаблон</button>
</form>

@if(auth()->user()->isAdmin())
{{-- Admin/superadmin: direct delete --}}
<form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" id="delete-course-form">
    @csrf @method('DELETE')
    <button type="button" onclick="showDeleteConfirm()">Видалити курс</button>
</form>
<div id="delete-confirm" class="confirm-delete" style="display:none;">
    <p><strong>Видалити курс «{{ $course->title }}»?</strong></p>
    <p class="text-sm text-muted">Ця дія незворотна. Введіть назву курсу для підтвердження:</p>
    <input type="text" id="confirm-title" placeholder="{{ $course->title }}">
    <div class="confirm-delete__row">
        <button type="button" id="confirm-delete-btn" disabled onclick="document.getElementById('delete-course-form').submit()">Так, видалити</button>
        <button type="button" onclick="hideDeleteConfirm()">Скасувати</button>
    </div>
</div>
<script>
function showDeleteConfirm() { document.getElementById('delete-confirm').style.display = 'block'; }
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

@elseif(auth()->user()->isTeacher())
{{-- Teacher: send deletion request --}}
@php
    $hasPendingDeletion = \App\Models\DeletionRequest::where('deletable_type', \App\Models\Course::class)
        ->where('deletable_id', $course->id)->pending()->exists();
@endphp
@if($hasPendingDeletion)
<div class="dr-pending">
    <strong>Запит на видалення надіслано</strong>
    <p class="text-sm text-muted">Очікується рішення адміністратора.</p>
</div>
@else
<button type="button" onclick="document.getElementById('del-request-form').style.display='block';this.style.display='none'"
        class="btn btn-danger">
    Видалити курс
</button>
<div id="del-request-form" class="dr-box" style="display:none;">
    <p class="dr-box__title">Запит на видалення курсу</p>
    <form method="POST" action="{{ route('deletion.store') }}">
        @csrf
        <input type="hidden" name="deletable_type" value="App\Models\Course">
        <input type="hidden" name="deletable_id" value="{{ $course->id }}">
        <textarea name="reason" rows="3" placeholder="Причина видалення (необов'язково)..."></textarea>
        <div class="flex-row mt-1">
            <button type="submit" class="btn btn-sm btn-danger">Надіслати запит</button>
            <button type="button" onclick="document.getElementById('del-request-form').style.display='none';this.closest('div').previousElementSibling.style.display=''"
                    class="btn btn-sm btn-ghost">
                Скасувати
            </button>
        </div>
    </form>
</div>
@if(session('deletion_requested'))
<p class="text-success mt-1">{{ session('deletion_requested') }}</p>
@endif
@if(session('deletion_pending'))
<p class="text-warn mt-1">{{ session('deletion_pending') }}</p>
@endif
@endif
@endif

<hr>
<h2>Співвикладачі</h2>
@if($course->coTeachers->count())
    @foreach($course->coTeachers as $coTeacher)
    <div>
        {{ $coTeacher->last_name }} {{ $coTeacher->first_name }} ({{ $coTeacher->role }})
        @if(auth()->user()->isAdmin())
        <form method="POST" action="{{ route('teacher.courses.coTeachers.remove', [$course, $coTeacher]) }}" class="form-inline">
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
<div class="students-table-wrap {{ $course->students->count() > 10 ? 'students-table-wrap--scrollable' : '' }}">
    <table class="data-table">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Статус</th>
                <th>Оплата</th>
                <th>Записаний</th>
            </tr>
        </thead>
        <tbody>
        @foreach($course->students as $student)
            <tr>
                <td><a href="{{ route('profile.show', $student) }}">{{ $student->last_name }} {{ $student->first_name }}</a></td>
                <td>
                    @switch($student->pivot->status)
                        @case('active') Активний @break
                        @case('completed') Завершив @break
                        @case('pending') Очікує @break
                        @default {{ $student->pivot->status }}
                    @endswitch
                </td>
                <td>{{ $student->pivot->is_paid ? '✅' : '❌' }}</td>
                <td>{{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d.m.Y') : '—' }}</td>
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