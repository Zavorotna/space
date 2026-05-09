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

    <div class="form-group">
        <label>Формат занять</label><br>
        <select name="schedule_mode" id="cr-mode" onchange="crToggleLoc(this.value)">
            <option value="online" @selected(old('schedule_mode','online')==='online')>Онлайн</option>
            <option value="offline" @selected(old('schedule_mode')==='offline')>Офлайн</option>
        </select>
    </div>
    <div id="cr-loc" class="schedule-loc-block" style="display:{{ old('schedule_mode')==='offline'?'flex':'none' }};">
        <div>
            <label>Локація</label><br>
            <select name="schedule_location_id" id="cr-loc-sel" onchange="crFilterRooms(this.value)">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                <option value="{{ $loc->id }}" @selected(old('schedule_location_id')==$loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Аудиторія</label><br>
            <select name="schedule_classroom_id" id="cr-room-sel">
                <option value="">— Оберіть —</option>
                @foreach($locations as $loc)
                    @foreach($loc->classrooms as $room)
                    <option value="{{ $room->id }}" data-location="{{ $loc->id }}"
                            @selected(old('schedule_classroom_id')==$room->id)>
                        {{ $loc->name }} — {{ $room->name }}
                    </option>
                    @endforeach
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group mt-1">
        <label>Дні та час занять</label>
        @php $oldTimes = old('schedule_times', []); $oldDays = old('schedule_days', []); @endphp
        @foreach([1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Нд'] as $num => $dayLabel)
        <div class="sched-day-row">
            <label class="sched-day-check">
                <input type="checkbox" name="schedule_days[]" value="{{ $num }}"
                       @checked(in_array($num, $oldDays))
                       onchange="schedToggleDay({{ $num }}, this.checked)">
                {{ $dayLabel }}
            </label>
            <div id="sched-times-{{ $num }}" class="sched-day-times"
                 style="display:{{ in_array($num, $oldDays) ? 'flex' : 'none' }}">
                <input type="time" name="schedule_times[{{ $num }}][start]"
                       value="{{ $oldTimes[$num]['start'] ?? '' }}"
                       id="sched-start-{{ $num }}"
                       onblur="schedAutoEnd({{ $num }})">
                <span>–</span>
                <input type="time" name="schedule_times[{{ $num }}][end]"
                       value="{{ $oldTimes[$num]['end'] ?? '' }}"
                       id="sched-end-{{ $num }}">
            </div>
        </div>
        @endforeach
    </div>

    <button type="submit" class="btn mt-2">Зберегти</button>
</form>

<script>
function crToggleLoc(v) { document.getElementById('cr-loc').style.display = v === 'offline' ? 'flex' : 'none'; }
function crFilterRooms(locId) {
    document.querySelectorAll('#cr-room-sel option[data-location]').forEach(o => {
        o.style.display = (!locId || o.dataset.location == locId) ? '' : 'none';
    });
    document.getElementById('cr-room-sel').value = '';
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
</script>
@endsection