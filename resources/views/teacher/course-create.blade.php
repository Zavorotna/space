@extends('layouts.app')
@section('title', 'Створення курсу')
@section('content')
<a href="{{ route('teacher.courses.index') }}">&larr; Курси</a>
<h1>Створення курсу</h1>
<form method="POST" action="{{ route('teacher.courses.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-group"><label><input type="checkbox" name="is_template" value="1" @checked(old('is_template'))> Зберегти як шаблон</label></div>
    <div class="form-group"><label>Фото курсу</label><input type="file" name="cover" accept="image/*"></div>
    <div class="form-group"><label>Назва курсу</label><input type="text" name="title" value="{{ old('title') }}" required></div>
    <div class="form-group"><label>Опис курсу</label><textarea name="description">{{ old('description') }}</textarea></div>

    {{-- Topics replace program --}}
    <div class="form-group">
        <label>Теми курсу</label>
        @php $oldTopics = old('topics', []); @endphp
        <div id="topics-list">
            @foreach($oldTopics as $i => $t)
            @if(!empty($t['title']))
            <div class="topic-row">
                <span class="topic-num">{{ $loop->iteration }}.</span>
                <input type="text" name="topics[{{ $i }}][title]" value="{{ $t['title'] }}" placeholder="Назва теми" style="flex:1">
                <button type="button" class="btn btn-xs btn-danger" onclick="this.closest('.topic-row').remove()">×</button>
            </div>
            @endif
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-ghost mt-1" onclick="addTopic()">+ Додати тему</button>
    </div>

    <div class="form-group"><label>Тип</label>
        <select name="type">
            <option value="group"      @selected(old('type','group')==='group')>Груповий</option>
            <option value="individual" @selected(old('type')==='individual')>Індивідуальний</option>
        </select>
    </div>

    {{-- Format below type --}}
    <div class="form-group">
        <label>Формат</label>
        <select name="schedule_mode" id="cr-mode" onchange="crToggleLoc(this.value)">
            <option value="online"  @selected(old('schedule_mode','online')==='online')>Онлайн</option>
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

    <div class="form-group"><label>Ціна (грн)</label><input type="number" name="price" step="0.01" value="{{ old('price', 0) }}"></div>
    <div class="form-group"><label>Період оплати</label>
        <select name="billing_period">
            <option value="monthly">Щомісячно</option>
            <option value="one_time">Разово</option>
            <option value="per_lesson">За заняття</option>
        </select>
    </div>
    <hr>
    <h3>Розклад занять</h3>
    <p class="text-xs text-muted">Якщо заповнено — заняття автоматично з'являться в графіку.</p>
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
                   id="sched-start-{{ $num }}" onblur="schedAutoEnd({{ $num }})">
            <span>–</span>
            <input type="time" name="schedule_times[{{ $num }}][end]"
                   value="{{ $oldTimes[$num]['end'] ?? '' }}"
                   id="sched-end-{{ $num }}">
        </div>
    </div>
    @endforeach

    <button type="submit" class="btn mt-2">Зберегти</button>
</form>

<script>
let _topicIdx = {{ count(old('topics', [])) }};
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