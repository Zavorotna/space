@extends('layouts.app')
@section('title', 'Редагування: ' . $course->title)
@section('content')
<h1>Редагування курсу: {{ $course->title }}</h1>

{{-- ══ MAIN FORM ══ --}}
<form method="POST" action="{{ route('teacher.courses.update', $course) }}" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="form-group"><label>Назва</label><input type="text" name="title" value="{{ $course->title }}" required></div>
    <div class="form-group"><label>Опис</label><textarea name="description">{{ $course->description }}</textarea></div>

    {{-- Topics replaces program textarea --}}
    <div class="form-group">
        <label>Теми курсу</label>
        <div id="topics-list">
            @foreach($course->topics as $topic)
            <div class="topic-row">
                <input type="hidden" name="topics[{{ $loop->index }}][id]" value="{{ $topic->id }}">
                <span class="topic-num">{{ $loop->iteration }}.</span>
                <input type="text" name="topics[{{ $loop->index }}][title]" value="{{ $topic->title }}" placeholder="Назва теми">
                <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('.topic-row').remove()">×</button>
            </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-ghost mt-1" onclick="addTopic()">+ Додати тему</button>
    </div>

    <div class="form-group"><label>Ціна (грн)</label><input type="number" name="price" step="0.01" value="{{ $course->price }}"></div>
    <div class="form-group"><label>Період оплати</label>
        <select name="billing_period">
            <option value="monthly"    @selected($course->billing_period==='monthly')>Щомісячно</option>
            <option value="one_time"   @selected($course->billing_period==='one_time')>Разово</option>
            <option value="per_lesson" @selected($course->billing_period==='per_lesson')>За заняття</option>
        </select>
    </div>
    @if(!$course->is_template)
    <div class="form-group"><label>Статус</label>
        <select name="status">
            @foreach(['waiting'=>'Очікування','enrolling'=>'Набір','active'=>'Активний','completed'=>'Завершений'] as $k=>$v)
                <option value="{{ $k }}" @selected($course->status===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="form-group"><label>Тип</label>
        <select name="type">
            <option value="group"      @selected($course->type==='group')>Груповий</option>
            <option value="individual" @selected($course->type==='individual')>Індивідуальний</option>
        </select>
    </div>

    {{-- Format moved here, below type --}}
    <div class="form-group">
        <label>Формат</label>
        <select name="schedule_mode" id="main-mode" onchange="toggleMainLoc(this.value)">
            <option value="online"  @selected(($course->schedule_mode ?? 'online')==='online')>Онлайн</option>
            <option value="offline" @selected($course->schedule_mode==='offline')>Офлайн</option>
        </select>
    </div>
    <div id="main-loc" class="schedule-loc-block" style="display:{{ $course->schedule_mode==='offline'?'flex':'none' }};">
        <div>
            <label>Локація</label><br>
            <select name="schedule_location_id" id="main-loc-sel" onchange="mainFilterRooms(this.value)">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected($course->schedule_location_id == $loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Аудиторія</label><br>
            <select name="schedule_classroom_id" id="main-room-sel">
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

    @if(!$course->is_template)
    <div class="form-group"><label>Telegram</label><input type="url" name="telegram_link" value="{{ $course->telegram_link }}"></div>
    <div class="form-group"><label>Дата початку</label><input type="date" name="start_date" value="{{ $course->start_date?->format('Y-m-d') }}"></div>
    <div class="form-group"><label>Дата закінчення</label><input type="date" name="end_date" value="{{ $course->end_date?->format('Y-m-d') }}"></div>
    <div class="form-group">
        <label><input type="checkbox" name="is_published" value="1" @checked($course->is_published)> Опубліковано</label>
    </div>
    @endif
    <div class="form-group">
        <label>Фото</label>
        @if($course->getFirstMediaUrl('cover'))
            <div><img src="{{ $course->getFirstMediaUrl('cover') }}" alt="" class="course-cover"></div>
        @endif
        <input type="file" name="cover" accept="image/*">
    </div>

    <hr>
    <h3>Розклад занять</h3>
    <p class="text-xs text-muted mb-1">Зміна розкладу видалить майбутні незаплановані заняття і перегенерує від сьогодні.</p>
    @php $scheduleTimes = $course->schedule_times ?? []; @endphp
    @foreach([1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Нд'] as $num => $dayLabel)
    @php
        $isChecked = is_array($course->schedule_days) && in_array($num, $course->schedule_days);
        $dayTime   = $scheduleTimes[$num] ?? $scheduleTimes[(string)$num] ?? null;
        $defStart  = $course->schedule_start_time ? substr($course->schedule_start_time, 0, 5) : '';
        $defEnd    = $course->schedule_end_time   ? substr($course->schedule_end_time, 0, 5)   : '';
    @endphp
    <div class="sched-day-row">
        <label class="sched-day-check">
            <input type="checkbox" name="schedule_days[]" value="{{ $num }}"
                   @checked($isChecked)
                   onchange="schedToggleDay({{ $num }}, this.checked)">
            {{ $dayLabel }}
        </label>
        <div id="sched-times-{{ $num }}" class="sched-day-times" style="display:{{ $isChecked ? 'flex' : 'none' }}">
            <input type="time" name="schedule_times[{{ $num }}][start]"
                   value="{{ $dayTime['start'] ?? $defStart }}"
                   id="sched-start-{{ $num }}" onblur="schedAutoEnd({{ $num }})">
            <span>–</span>
            <input type="time" name="schedule_times[{{ $num }}][end]"
                   value="{{ $dayTime['end'] ?? $defEnd }}"
                   id="sched-end-{{ $num }}">
        </div>
    </div>
    @endforeach

    <button type="submit" class="btn mt-2">Зберегти</button>
</form>

{{-- DUPLICATE / DELETE --}}
<div class="flex-row mt-2">
    <form method="POST" action="{{ route('teacher.courses.duplicate', $course) }}"
          onsubmit="this.querySelector('button').disabled=true">
        @csrf
        <button type="submit" class="btn btn-ghost">Скопіювати як шаблон</button>
    </form>

    @if(auth()->user()->isAdmin())
    <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}" id="delete-course-form">
        @csrf @method('DELETE')
        <button type="button" class="btn btn-danger" onclick="showDeleteConfirm()">Видалити курс</button>
    </form>
    @elseif(auth()->user()->isTeacher())
    @php $hasPendingDeletion = \App\Models\DeletionRequest::where('deletable_type', \App\Models\Course::class)->where('deletable_id', $course->id)->pending()->exists(); @endphp
    @if(!$hasPendingDeletion)
    <button type="button" class="btn btn-danger"
            onclick="document.getElementById('del-request-form').style.display='block';this.style.display='none'">Видалити курс</button>
    @else
    <span class="text-warn text-sm">Запит на видалення надіслано</span>
    @endif
    @endif
</div>

@if(auth()->user()->isAdmin())
<div id="delete-confirm" class="confirm-delete" style="display:none;">
    <p><strong>Видалити курс «{{ $course->title }}»?</strong></p>
    <p class="text-sm text-muted">Введіть назву курсу для підтвердження:</p>
    <input type="text" id="confirm-title" placeholder="{{ $course->title }}">
    <div class="confirm-delete__row">
        <button type="button" id="confirm-delete-btn" disabled class="btn btn-danger"
                onclick="document.getElementById('delete-course-form').submit()">Так, видалити</button>
        <button type="button" class="btn btn-ghost" onclick="hideDeleteConfirm()">Скасувати</button>
    </div>
</div>
@elseif(auth()->user()->isTeacher() && isset($hasPendingDeletion) && !$hasPendingDeletion)
<div id="del-request-form" class="dr-box" style="display:none;">
    <p class="dr-box__title">Запит на видалення курсу</p>
    <form method="POST" action="{{ route('deletion.store') }}">
        @csrf
        <input type="hidden" name="deletable_type" value="App\Models\Course">
        <input type="hidden" name="deletable_id" value="{{ $course->id }}">
        <textarea name="reason" rows="3" placeholder="Причина (необов'язково)..."></textarea>
        <div class="flex-row mt-1">
            <button type="submit" class="btn btn-sm btn-danger">Надіслати</button>
            <button type="button" class="btn btn-sm btn-ghost"
                    onclick="this.closest('.dr-box').style.display='none';this.closest('.dr-box').previousElementSibling.style.display=''">Скасувати</button>
        </div>
    </form>
</div>
@endif

<hr>

@if(!$course->is_template)
{{-- ══ TEACHERS SECTION ══ --}}
<h2>Викладачі</h2>
<div class="card-panel">
    @if(auth()->user()->isAdmin())
    <div class="form-group">
        <label><strong>Основний викладач</strong> — кому відображаються заняття в графіку</label>
        <form method="POST" action="{{ route('teacher.courses.teacher', $course) }}" class="flex-row mt-1">
            @csrf @method('PUT')
            <select name="teacher_id" required>
                @foreach($teachers as $t)
                <option value="{{ $t->id }}" @selected($course->teacher_id === $t->id)>
                    {{ $t->last_name }} {{ $t->first_name }} ({{ $t->role }})
                </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm">Зберегти</button>
        </form>
    </div>
    @else
    <p><strong>Викладач:</strong> {{ $course->teacher?->full_name ?? '—' }}</p>
    @endif

    <div class="mt-2">
        <strong>Співвикладачі</strong>
        @if($course->coTeachers->count())
        @foreach($course->coTeachers as $coTeacher)
        <div class="flex-between mb-1">
            <span>{{ $coTeacher->last_name }} {{ $coTeacher->first_name }} ({{ $coTeacher->role }})</span>
            @if(auth()->user()->isAdmin())
            <form method="POST" action="{{ route('teacher.courses.coTeachers.remove', [$course, $coTeacher]) }}" class="form-inline">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('Видалити?')" class="btn btn-xs btn-danger">×</button>
            </form>
            @endif
        </div>
        @endforeach
        @else
        <p class="text-muted text-sm">Немає.</p>
        @endif
    </div>

    @if(auth()->user()->isAdmin())
    <button type="button" class="btn btn-sm btn-ghost mt-1"
            onclick="this.style.display='none';document.getElementById('coteacher-form').style.display='block'">
        + Додати співвикладача
    </button>
    <div id="coteacher-form" style="display:none;" class="mt-1">
        <form method="POST" action="{{ route('teacher.courses.coTeachers.add', $course) }}" class="flex-row">
            @csrf
            <select name="user_id" required>
                <option value="">— Оберіть —</option>
                @foreach($teachers->filter(fn($t) => $t->id !== $course->teacher_id && !$course->coTeachers->contains($t->id)) as $t)
                <option value="{{ $t->id }}">{{ $t->last_name }} {{ $t->first_name }} ({{ $t->role }})</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Додати</button>
        </form>
    </div>
    @endif
</div>

<hr>

{{-- ══ STUDENTS SECTION ══ --}}
<div class="section-header">
    <h2>Студенти ({{ $course->students->count() }})</h2>
    <a href="{{ route('teacher.courses.applications', $course) }}" class="btn btn-sm btn-ghost">Заявки</a>
</div>

@if($course->students->count())
<div class="students-table-wrap {{ $course->students->count() > 10 ? 'students-table-wrap--scrollable' : '' }}">
    <table class="data-table">
        <thead>
            <tr><th>Студент</th><th>Статус</th><th>Оплата</th><th>Записаний</th></tr>
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
<p class="text-muted text-sm">Студентів ще немає.</p>
@endif

<h3 class="mt-2">Додати студента</h3>
<form method="POST" action="{{ route('teacher.courses.addStudent', $course) }}" class="flex-row">
    @csrf
    <input type="number" name="user_id" placeholder="ID студента" style="width:110px">
    <span class="text-muted text-sm">або</span>
    <input type="text" name="phone" placeholder="Номер телефону" style="width:160px">
    <button type="submit" class="btn btn-sm">Додати</button>
</form>
@endif

<hr>

{{-- ══ HOMEWORK SECTION ══ --}}
<div class="section-header">
    <h2>Домашні завдання</h2>
    <button type="button" class="btn btn-sm btn-ghost" id="hw-toggle"
            onclick="toggleSection('hw-form','hw-toggle')">+ Додати ДЗ</button>
</div>
@foreach($course->homeworkAssignments as $hw)
<div class="flex-between mb-1">
    <div>
        <strong>{{ $hw->title }}</strong>
        <span class="text-muted text-sm">({{ $hw->difficulty }}) — до {{ $hw->deadline->format('d.m.Y') }}</span>
    </div>
    <a href="{{ route('teacher.homework.submissions', $hw) }}" class="text-sm">Здачі ({{ $hw->submissions->count() }})</a>
</div>
@endforeach
<div id="hw-form" style="display:none;" class="card-panel mt-1">
    <form method="POST" action="{{ route('teacher.homework.store', $course) }}">
        @csrf
        <div class="form-group"><label>Назва ДЗ</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Опис</label><textarea name="description"></textarea></div>
        <div class="flex-row">
            <div class="form-group">
                <label>Складність</label>
                <select name="difficulty">
                    <option value="easy">Легка (5 монет)</option>
                    <option value="medium" selected>Середня (15 монет)</option>
                    <option value="hard">Важка (25 монет)</option>
                </select>
            </div>
            <div class="form-group"><label>Дедлайн</label><input type="date" name="deadline" required></div>
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Зберегти ДЗ</button>
    </form>
</div>

<hr>

{{-- ══ TESTS SECTION ══ --}}
<div class="section-header">
    <h2>Тести</h2>
    <a href="{{ route('tests.index') }}" class="btn btn-sm btn-blue">+ Новий тест</a>
</div>
@forelse($course->tests as $test)
<div class="flex-between mb-1 flex-start">
    <div>
        <strong>{{ $test->title }}</strong>
        <span class="text-muted text-sm">({{ $test->questions->count() }} питань, {{ $test->passing_score }}%)</span>
        <a href="{{ route('tests.edit', $test) }}" class="text-sm">Ред.</a>
        <a href="{{ route('tests.statistics', $test) }}" class="text-sm">Стат.</a>
    </div>
    @if($course->topics->count())
    <form method="POST" action="{{ route('tests.update', $test) }}" class="flex-row">
        @csrf @method('PUT')
        <input type="hidden" name="title" value="{{ $test->title }}">
        <input type="hidden" name="description" value="{{ $test->description ?? '' }}">
        <input type="hidden" name="passing_score" value="{{ $test->passing_score }}">
        <label class="text-xs text-muted">Після теми:</label>
        <select name="activation_topic_id" style="font-size:.82rem">
            <option value="">Одразу</option>
            @foreach($course->topics as $topic)
            <option value="{{ $topic->id }}" @selected($test->activation_topic_id == $topic->id)>
                {{ $loop->iteration }}. {{ \Illuminate\Support\Str::limit($topic->title, 35) }}
            </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-xs">OK</button>
    </form>
    @endif
</div>
@empty
<p class="text-muted text-sm">Тестів ще немає. <a href="{{ route('tests.index') }}">Створити на сторінці тестів →</a></p>
@endforelse

<hr>

{{-- ══ GRADUATION PROJECT SECTION ══ --}}
<div class="section-header">
    <h2>Випускний проєкт</h2>
</div>
@if($course->graduationProject)
<p>{{ $course->graduationProject->title }} — до {{ $course->graduationProject->deadline->format('d.m.Y') }}</p>
<a href="{{ route('teacher.graduation.submissions', $course->graduationProject) }}" class="text-sm">Здачі</a>
@else
<button type="button" class="btn btn-sm btn-ghost" id="grad-toggle"
        onclick="toggleSection('grad-form','grad-toggle')">+ Додати випускний проєкт</button>
<div id="grad-form" style="display:none;" class="card-panel mt-1">
    <form method="POST" action="{{ route('teacher.graduation.store', $course) }}">
        @csrf
        <div class="form-group"><label>Назва</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Опис</label><textarea name="description"></textarea></div>
        <div class="form-group"><label>Дедлайн</label><input type="date" name="deadline" required></div>
        <button type="submit" class="btn btn-sm btn-primary">Зберегти</button>
    </form>
</div>
@endif

<hr>

{{-- ══ ADDITIONAL MATERIALS SECTION ══ --}}
<div class="section-header">
    <h2>Додаткові матеріали</h2>
    <button type="button" class="btn btn-sm btn-ghost" id="mat-toggle"
            onclick="toggleSection('mat-form','mat-toggle')">+ Додати матеріал</button>
</div>
@foreach($course->additionalMaterials as $mat)
<div class="mb-1">
    <strong>{{ $mat->title }}</strong>
    @if($mat->url) <a href="{{ $mat->url }}" target="_blank" class="text-sm">↗</a> @endif
</div>
@endforeach
<div id="mat-form" style="display:none;" class="card-panel mt-1">
    <form method="POST" action="{{ route('teacher.materials.store', $course) }}">
        @csrf
        <div class="form-group"><label>Назва</label><input type="text" name="title" required></div>
        <div class="form-group"><label>Опис</label><textarea name="description"></textarea></div>
        <div class="form-group"><label>Посилання</label><input type="url" name="url" placeholder="https://..."></div>
        <button type="submit" class="btn btn-sm btn-primary">Зберегти</button>
    </form>
</div>

<script>
function toggleSection(id, btnId) {
    const el = document.getElementById(id);
    const wasHidden = el.style.display === 'none';
    el.style.display = wasHidden ? 'block' : 'none';
    if (wasHidden && btnId) {
        const btn = document.getElementById(btnId);
        if (btn) btn.style.display = 'none';
    }
}
let _topicIdx = {{ $course->topics->count() }};
function addTopic() {
    const list = document.getElementById('topics-list');
    const idx  = _topicIdx++;
    const num  = list.children.length + 1;
    const div  = document.createElement('div');
    div.className = 'topic-row';
    div.innerHTML = `<span class="topic-num">${num}.</span>
        <input type="text" name="topics[${idx}][title]" placeholder="Назва теми" style="flex:1">
        <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('.topic-row').remove()">×</button>`;
    list.appendChild(div);
}
function toggleMainLoc(v) {
    document.getElementById('main-loc').style.display = v === 'offline' ? 'flex' : 'none';
}
function mainFilterRooms(locId) {
    document.querySelectorAll('#main-room-sel option[data-location]').forEach(o => {
        o.style.display = (!locId || o.dataset.location == locId) ? '' : 'none';
    });
    document.getElementById('main-room-sel').value = '';
}
function schedToggleDay(day, show) {
    document.getElementById('sched-times-' + day).style.display = show ? 'flex' : 'none';
}
function schedAutoEnd(day) {
    const s = document.getElementById('sched-start-' + day);
    const e = document.getElementById('sched-end-' + day);
    if (!s.value || e.value) return;
    const [h, m] = s.value.split(':').map(Number);
    const t = h * 60 + m + 120;
    e.value = String(Math.floor(t / 60) % 24).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0');
}
function showDeleteConfirm() { document.getElementById('delete-confirm').style.display = 'block'; }
function hideDeleteConfirm() {
    document.getElementById('delete-confirm').style.display = 'none';
    document.getElementById('confirm-title').value = '';
    document.getElementById('confirm-delete-btn').disabled = true;
}
document.addEventListener('DOMContentLoaded', function () {
    const inp = document.getElementById('confirm-title');
    if (inp) inp.addEventListener('input', function () {
        document.getElementById('confirm-delete-btn').disabled = this.value !== '{{ addslashes($course->title) }}';
    });
});
</script>
@endsection