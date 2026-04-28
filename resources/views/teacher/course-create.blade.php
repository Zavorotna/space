@extends('layouts.app')
@section('title', 'Створення курсу')
@section('content')
<a href="{{ route('teacher.courses.index') }}">&larr; Курси</a>
<h1>Створення курсу</h1>
<form method="POST" action="{{ route('teacher.courses.store') }}" enctype="multipart/form-data">
    @csrf
    <div><label><input type="checkbox" name="is_template" value="1" @checked(old('is_template'))> Зберегти як шаблон</label></div>
    <div><label>Фото курсу</label><input type="file" name="cover" accept="image/*"></div>
    <div><label>Назва курсу</label><input type="text" name="title" value="{{ old('title') }}" required></div>
    <div><label>Опис курсу</label><textarea name="description">{{ old('description') }}</textarea></div>
    <div><label>Програма</label><textarea name="program">{{ old('program') }}</textarea></div>
    <div><label>Тип</label>
        <select name="type"><option value="group">Груповий</option><option value="individual">Індивідуальний</option></select>
    </div>
    <div><label>Ціна (грн)</label><input type="number" name="price" step="0.01" value="{{ old('price', 0) }}"></div>
    <div><label>Період оплати</label>
        <select name="billing_period"><option value="monthly">Щомісячно</option><option value="one_time">Разово</option><option value="per_lesson">За заняття</option></select>
    </div>
    <div><label>Telegram посилання</label><input type="url" name="telegram_link" value="{{ old('telegram_link') }}"></div>
    <div><label>Дата відкритого заняття</label><input type="date" name="intro_date" value="{{ old('intro_date') }}"></div>
    <div><label>Дата початку</label><input type="date" name="start_date" value="{{ old('start_date') }}"></div>
    <div><label>Дата закінчення</label><input type="date" name="end_date" value="{{ old('end_date') }}"></div>
    <div><label><input type="checkbox" name="has_graduation_project" value="1" checked> Є випускний проєкт</label></div>

    <hr>
    <h3>Розклад занять (для автогенерації)</h3>
    <p class="text-sm text-muted">Якщо заповнено — заняття будуть автоматично додані до розкладу при призначенні викладача.</p>

    <div>
        <label>Дні тижня</label><br>
        @foreach([1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Нд'] as $num => $label)
        <label class="schedule-day-label">
            <input type="checkbox" name="schedule_days[]" value="{{ $num }}"
                   @checked(in_array($num, old('schedule_days', [])))>
            {{ $label }}
        </label>
        @endforeach
    </div>
    <div class="schedule-time-row">
        <div><label>Початок заняття</label><br><input type="time" name="schedule_start_time" value="{{ old('schedule_start_time') }}"></div>
        <div><label>Кінець заняття</label><br><input type="time" name="schedule_end_time" value="{{ old('schedule_end_time') }}"></div>
        <div>
            <label>Формат</label><br>
            <select name="schedule_mode" id="sched-mode-create" onchange="toggleSchedLocation('create',this.value)">
                <option value="online" @selected(old('schedule_mode','online')==='online')>Онлайн</option>
                <option value="offline" @selected(old('schedule_mode')==='offline')>Офлайн</option>
            </select>
        </div>
    </div>
    <div id="sched-loc-create" class="schedule-loc-block" style="display:{{ old('schedule_mode')==='offline'?'block':'none' }};">
        <div>
            <label>Локація</label><br>
            <select name="schedule_location_id" id="sched-loc-sel-create" onchange="filterClassrooms('create',this.value)">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected(old('schedule_location_id')==$loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-1">
            <label>Аудиторія</label><br>
            <select name="schedule_classroom_id" id="sched-room-sel-create">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                    @foreach($loc->classrooms as $room)
                    <option value="{{ $room->id }}" data-location="{{ $loc->id }}" @selected(old('schedule_classroom_id')==$room->id)>
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
@endsection