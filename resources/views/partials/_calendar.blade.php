{{--
  Variables:
    $schedDate      Carbon
    $schedMode      string (day|week|month)
    $schedLessons   Collection<Lesson>
    $schedEvents    Collection<CalendarEvent>
    $schedLocations Collection<Location>   (optional, for add form)
    $schedCourses   Collection<Course>     (optional, for add form)
    $canEdit        bool
--}}
@php
    $cur    = $schedDate;
    $today  = today()->toDateString();

    $prevDate = match($schedMode) {
        'week'  => $cur->copy()->subWeek()->toDateString(),
        'month' => $cur->copy()->subMonth()->toDateString(),
        default => $cur->copy()->subDay()->toDateString(),
    };
    $nextDate = match($schedMode) {
        'week'  => $cur->copy()->addWeek()->toDateString(),
        'month' => $cur->copy()->addMonth()->toDateString(),
        default => $cur->copy()->addDay()->toDateString(),
    };

    $periodLabel = match($schedMode) {
        'week'  => $cur->copy()->startOfWeek()->translatedFormat('d F') . ' – ' . $cur->copy()->endOfWeek()->translatedFormat('d F Y'),
        'month' => $cur->translatedFormat('F Y'),
        default => $cur->translatedFormat('l, d F Y'),
    };

    $lessonsByDate = $schedLessons->groupBy(fn($l) => $l->date->format('Y-m-d'));
    $eventsByDate  = $schedEvents->groupBy(fn($e) => $e->date->format('Y-m-d'));

    $evColors = ['graduation' => '#f5a623', 'meeting' => '#27ae60', 'holiday' => '#8e44ad', 'other' => '#7f8c8d'];
    $evLabels = ['graduation' => 'Випуск', 'meeting' => 'Зустріч', 'holiday' => 'Вихідний', 'other' => 'Подія'];

    $defaultDate = $schedMode === 'day' ? $cur->toDateString() : $today;
@endphp

<div class="cal-wrap">

    {{-- ── Header ── --}}
    <div class="cal-header">
        <div class="cal-tabs">
            @foreach(['day' => 'День', 'week' => 'Тиждень', 'month' => 'Місяць'] as $m => $label)
            <a href="{{ route('dashboard', ['schedule_mode' => $m, 'schedule_date' => $cur->toDateString()]) }}"
               class="cal-tab {{ $schedMode === $m ? 'cal-tab--active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="cal-nav">
            <a href="{{ route('dashboard', ['schedule_mode' => $schedMode, 'schedule_date' => $prevDate]) }}" class="cal-arrow">&#8249;</a>
            <span class="cal-period">{{ $periodLabel }}</span>
            <a href="{{ route('dashboard', ['schedule_mode' => $schedMode, 'schedule_date' => $nextDate]) }}" class="cal-arrow">&#8250;</a>
            @if($cur->toDateString() !== $today)
            <a href="{{ route('dashboard', ['schedule_mode' => $schedMode, 'schedule_date' => $today]) }}" class="cal-today-link">Сьогодні</a>
            @endif
        </div>

        @if(!empty($canEdit))
        <div class="cal-actions">
            <button type="button" onclick="calToggleForm('cal-lesson-form')" class="cal-btn cal-btn--blue">+ Заняття</button>
            <button type="button" onclick="calToggleForm('cal-event-form')"  class="cal-btn cal-btn--orange">+ Подія</button>
        </div>
        @endif
    </div>

    {{-- ── Add Lesson Form ── --}}
    @if(!empty($canEdit))
    <div id="cal-lesson-form" class="cal-form" style="display:none;">
        <p class="cal-form-title">Нове заняття</p>
        <form method="POST" action="{{ route('teacher.schedule.store') }}">
            @csrf
            <div class="cal-form-grid">
                <div class="cal-field">
                    <label>Курс *</label>
                    <select name="course_id" required>
                        <option value="">Оберіть курс</option>
                        @foreach($schedCourses ?? [] as $c)
                        <option value="{{ $c->id }}">{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cal-field">
                    <label>Тема</label>
                    <input type="text" name="title" placeholder="Необов'язково">
                </div>
                <div class="cal-field">
                    <label>Формат *</label>
                    <select name="mode">
                        <option value="offline">Офлайн</option>
                        <option value="online">Онлайн</option>
                    </select>
                </div>
                <div class="cal-field">
                    <label>Локація</label>
                    <select name="location_id">
                        <option value="">—</option>
                        @foreach($schedLocations ?? [] as $loc)
                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="cal-field">
                    <label>Аудиторія</label>
                    <select name="classroom_id">
                        <option value="">—</option>
                        @foreach($schedLocations ?? [] as $loc)
                            @foreach($loc->classrooms as $room)
                            <option value="{{ $room->id }}">{{ $loc->name }} — {{ $room->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="cal-field">
                    <label>Дата *</label>
                    <input type="date" name="date" value="{{ $defaultDate }}" required>
                </div>
                <div class="cal-field">
                    <label>Початок *</label>
                    <input type="time" name="start_time" required>
                </div>
                <div class="cal-field">
                    <label>Кінець *</label>
                    <input type="time" name="end_time" required>
                </div>
            </div>
            <div class="cal-form-actions">
                <button type="submit" class="cal-btn cal-btn--blue">Зберегти</button>
                <button type="button" onclick="calToggleForm('cal-lesson-form')" class="cal-btn cal-btn--ghost">Скасувати</button>
            </div>
        </form>
    </div>

    {{-- ── Add Event Form ── --}}
    <div id="cal-event-form" class="cal-form" style="display:none;">
        <p class="cal-form-title">Нова подія</p>
        <form method="POST" action="{{ route('teacher.events.store') }}">
            @csrf
            <div class="cal-form-grid">
                <div class="cal-field">
                    <label>Назва *</label>
                    <input type="text" name="title" required placeholder="Назва події">
                </div>
                <div class="cal-field">
                    <label>Тип *</label>
                    <select name="type" required>
                        <option value="graduation">Випуск</option>
                        <option value="meeting">Зустріч</option>
                        <option value="holiday">Вихідний</option>
                        <option value="other">Інше</option>
                    </select>
                </div>
                <div class="cal-field">
                    <label>Дата *</label>
                    <input type="date" name="date" value="{{ $defaultDate }}" required>
                </div>
                <div class="cal-field">
                    <label>Початок</label>
                    <input type="time" name="start_time">
                </div>
                <div class="cal-field">
                    <label>Кінець</label>
                    <input type="time" name="end_time">
                </div>
                <div class="cal-field cal-field--wide">
                    <label>Опис</label>
                    <textarea name="description" rows="2" placeholder="Необов'язково"></textarea>
                </div>
            </div>
            <div class="cal-form-actions">
                <button type="submit" class="cal-btn cal-btn--orange">Зберегти</button>
                <button type="button" onclick="calToggleForm('cal-event-form')" class="cal-btn cal-btn--ghost">Скасувати</button>
            </div>
        </form>
    </div>
    @endif

    {{-- ════════════════════════════════ DAY VIEW ════════════════════════════════ --}}
    @if($schedMode === 'day')
    @php
        $dayKey     = $cur->toDateString();
        $dayLessons = $lessonsByDate->get($dayKey, collect());
        $dayEvents  = $eventsByDate->get($dayKey, collect());
        $allItems   = $dayLessons->map(fn($l) => ['kind'=>'lesson','time'=>$l->start_time,'obj'=>$l])
                        ->merge($dayEvents->map(fn($e) => ['kind'=>'event','time'=>$e->start_time ?? '00:00','obj'=>$e]))
                        ->sortBy('time');
    @endphp
    <div class="cal-day">
        @forelse($allItems as $item)
            @if($item['kind'] === 'lesson')
            @php $l = $item['obj']; @endphp
            <div class="cal-item cal-item--lesson">
                <div class="cal-item-time">{{ substr($l->start_time,0,5) }}<br>{{ substr($l->end_time,0,5) }}</div>
                <div class="cal-item-body">
                    <strong>{{ $l->course->title }}</strong>
                    @if($l->title) <span class="cal-sub"> · {{ $l->title }}</span> @endif
                    <div class="cal-meta">
                        <span class="cal-badge-mode">{{ $l->mode === 'online' ? 'Онлайн' : 'Офлайн' }}</span>
                        @if($l->location) {{ $l->location->name }} @endif
                        @if($l->classroom) ({{ $l->classroom->name }}) @endif
                        @isset($l->teacher) · {{ $l->teacher->full_name }} @endisset
                    </div>
                </div>
            </div>
            @else
            @php $e = $item['obj']; $ec = $evColors[$e->type] ?? '#7f8c8d'; @endphp
            <div class="cal-item" style="border-left-color:{{ $ec }};">
                <div class="cal-item-time">
                    {{ $e->start_time ? substr($e->start_time,0,5) : '—' }}
                    @if($e->end_time)<br>{{ substr($e->end_time,0,5) }}@endif
                </div>
                <div class="cal-item-body">
                    <strong>{{ $e->title }}</strong>
                    <span class="cal-badge-ev" style="background:{{ $ec }};">{{ $evLabels[$e->type] ?? 'Подія' }}</span>
                    @if($e->description) <div class="cal-meta">{{ $e->description }}</div> @endif
                </div>
                @if(!empty($canEdit))
                <form method="POST" action="{{ route('teacher.events.destroy', $e) }}" onsubmit="return confirm('Видалити подію?')" style="margin-left:auto;">
                    @csrf @method('DELETE')
                    <button type="submit" class="cal-del-btn">✕</button>
                </form>
                @endif
            </div>
            @endif
        @empty
        <p class="cal-empty">Немає занять та подій.</p>
        @endforelse
    </div>

    {{-- ════════════════════════════════ WEEK VIEW ════════════════════════════════ --}}
    @elseif($schedMode === 'week')
    @php $weekStart = $cur->copy()->startOfWeek(); @endphp
    <div class="cal-week">
        @for($d = $weekStart->copy(); $d <= $weekStart->copy()->endOfWeek(); $d->addDay())
        @php
            $key = $d->format('Y-m-d');
            $dl  = $lessonsByDate->get($key, collect());
            $de  = $eventsByDate->get($key, collect());
            $isT = $key === $today;
        @endphp
        <div class="cal-week-col {{ $isT ? 'cal-week-col--today' : '' }}">
            <a href="{{ route('dashboard', ['schedule_mode'=>'day','schedule_date'=>$key]) }}" class="cal-week-head">
                <span class="cal-week-dname">{{ $d->translatedFormat('D') }}</span>
                <span class="cal-week-num {{ $isT ? 'cal-week-num--today' : '' }}">{{ $d->day }}</span>
            </a>
            @foreach($dl as $l)
            <div class="cal-week-item cal-week-item--lesson">
                <div class="cal-wi-time">{{ substr($l->start_time,0,5) }}</div>
                <div class="cal-wi-title">{{ $l->course->title }}</div>
            </div>
            @endforeach
            @foreach($de as $e)
            @php $ec = $evColors[$e->type] ?? '#7f8c8d'; @endphp
            <div class="cal-week-item" style="border-left:3px solid {{ $ec }}; background:#fafafa;">
                <div class="cal-wi-time">{{ $e->start_time ? substr($e->start_time,0,5) : '' }}</div>
                <div class="cal-wi-title">{{ $e->title }}</div>
            </div>
            @endforeach
            @if($dl->isEmpty() && $de->isEmpty())
            <div class="cal-week-empty">—</div>
            @endif
        </div>
        @endfor
    </div>

    {{-- ════════════════════════════════ MONTH VIEW ════════════════════════════════ --}}
    @elseif($schedMode === 'month')
    @php
        $mStart = $cur->copy()->startOfMonth();
        $mEnd   = $cur->copy()->endOfMonth();
        $cell   = $mStart->copy()->startOfWeek();
    @endphp
    <div class="cal-month-wrap">
        <div class="cal-month-head">
            @foreach(['ПН','ВТ','СР','ЧТ','ПТ','СБ','НД'] as $dn)
            <div>{{ $dn }}</div>
            @endforeach
        </div>
        <div class="cal-month-grid">
        @while($cell <= $mEnd->copy()->endOfWeek())
        @php
            $key    = $cell->format('Y-m-d');
            $lCnt   = $lessonsByDate->get($key, collect())->count();
            $eCnt   = $eventsByDate->get($key, collect())->count();
            $inMon  = $cell->month === $mStart->month;
            $isT    = $key === $today;
        @endphp
        <div class="cal-month-cell {{ !$inMon ? 'cal-mc--out' : '' }} {{ $isT ? 'cal-mc--today' : '' }}">
            @if($lCnt || $eCnt)
            <a href="{{ route('dashboard', ['schedule_mode'=>'day','schedule_date'=>$key]) }}" class="cal-mc-num cal-mc-num--link">{{ $cell->day }}</a>
            @else
            <span class="cal-mc-num">{{ $cell->day }}</span>
            @endif
            <div class="cal-mc-dots">
                @if($lCnt) <span class="cal-dot cal-dot--blue" title="{{ $lCnt }} занять"></span> @endif
                @if($eCnt) <span class="cal-dot cal-dot--orange" title="{{ $eCnt }} подій"></span> @endif
            </div>
        </div>
        @php $cell->addDay(); @endphp
        @endwhile
        </div>
    </div>
    @endif

</div>{{-- .cal-wrap --}}

@once
<style>
/* ─── Calendar wrapper ─── */
.cal-wrap{background:#fff;border-radius:10px;box-shadow:0 1px 8px rgba(0,0,0,.1);margin-bottom:24px;overflow:hidden;}

/* Header */
.cal-header{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 14px;background:#f7f8fa;border-bottom:1px solid #e8e8e8;}
.cal-tabs{display:flex;gap:2px;}
.cal-tab{padding:4px 11px;border-radius:5px;text-decoration:none;color:#555;font-size:.85rem;}
.cal-tab--active{background:#1a1a2e;color:#fff;font-weight:600;}
.cal-nav{display:flex;align-items:center;gap:8px;flex:1;min-width:0;}
.cal-arrow{text-decoration:none;color:#1a1a2e;font-size:1.4rem;padding:0 6px;line-height:1;}
.cal-period{font-size:.9rem;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cal-today-link{font-size:.78rem;color:#4a90d9;text-decoration:none;border:1px solid #4a90d9;border-radius:4px;padding:2px 7px;white-space:nowrap;}
.cal-actions{display:flex;gap:6px;margin-left:auto;}
.cal-btn{padding:6px 13px;border:none;border-radius:5px;cursor:pointer;font-size:.83rem;font-weight:500;}
.cal-btn--blue{background:#4a90d9;color:#fff;}
.cal-btn--orange{background:#f5a623;color:#fff;}
.cal-btn--ghost{background:#e8e8e8;color:#555;}

/* Forms */
.cal-form{padding:14px 16px;border-bottom:1px solid #e8e8e8;background:#fafbfc;}
.cal-form-title{margin:0 0 10px;font-weight:600;font-size:.95rem;color:#1a1a2e;}
.cal-form-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:8px;}
.cal-field{display:flex;flex-direction:column;gap:3px;}
.cal-field--wide{grid-column:1/-1;}
.cal-field label{font-size:.75rem;color:#888;font-weight:500;}
.cal-field input,.cal-field select,.cal-field textarea{padding:5px 8px;border:1px solid #ddd;border-radius:4px;font-size:.85rem;width:100%;}
.cal-form-actions{margin-top:10px;display:flex;gap:8px;}

/* Day view */
.cal-day{padding:10px 14px;display:flex;flex-direction:column;gap:8px;min-height:80px;}
.cal-item{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border-radius:6px;border-left:4px solid #ccc;background:#f9f9f9;}
.cal-item--lesson{border-left-color:#4a90d9;background:#f0f6ff;}
.cal-item-time{font-size:.78rem;color:#888;white-space:nowrap;min-width:42px;text-align:right;padding-top:2px;line-height:1.5;}
.cal-item-body{flex:1;min-width:0;}
.cal-sub{color:#666;font-size:.86rem;}
.cal-meta{font-size:.76rem;color:#aaa;margin-top:3px;}
.cal-badge-mode{display:inline-block;padding:1px 5px;border-radius:3px;background:#e8e8e8;color:#555;font-size:.72rem;margin-right:4px;}
.cal-badge-ev{display:inline-block;padding:1px 6px;border-radius:3px;color:#fff;font-size:.72rem;font-weight:600;margin-left:6px;}
.cal-del-btn{background:none;border:none;color:#ccc;cursor:pointer;font-size:.78rem;padding:2px 5px;}
.cal-del-btn:hover{color:#e74c3c;}
.cal-empty{color:#bbb;font-size:.88rem;text-align:center;padding:24px 0;}

/* Week view */
.cal-week{display:grid;grid-template-columns:repeat(7,1fr);border-top:1px solid #eee;}
.cal-week-col{border-right:1px solid #eee;padding:6px 4px;min-height:120px;}
.cal-week-col:last-child{border-right:none;}
.cal-week-col--today{background:#f5f8ff;}
.cal-week-head{display:flex;flex-direction:column;align-items:center;text-decoration:none;margin-bottom:6px;}
.cal-week-dname{font-size:.72rem;color:#999;text-transform:uppercase;}
.cal-week-num{display:flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-size:.9rem;color:#333;}
.cal-week-num--today{background:#1a1a2e;color:#fff;font-weight:700;}
.cal-week-item{border-radius:3px;padding:3px 5px;margin-bottom:3px;overflow:hidden;}
.cal-week-item--lesson{background:#e8f0ff;border-left:3px solid #4a90d9;}
.cal-wi-time{font-size:.66rem;color:#999;}
.cal-wi-title{font-size:.74rem;color:#333;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cal-week-empty{color:#ddd;font-size:.75rem;text-align:center;margin-top:8px;}

/* Month view */
.cal-month-wrap{}
.cal-month-head{display:grid;grid-template-columns:repeat(7,1fr);background:#f7f8fa;border-top:1px solid #eee;}
.cal-month-head>div{text-align:center;padding:6px 0;font-size:.75rem;color:#999;font-weight:600;}
.cal-month-grid{display:grid;grid-template-columns:repeat(7,1fr);}
.cal-month-cell{border-right:1px solid #eee;border-bottom:1px solid #eee;padding:5px 6px;min-height:56px;}
.cal-mc--out{opacity:.3;}
.cal-mc--today .cal-mc-num{background:#1a1a2e;color:#fff;border-radius:50%;}
.cal-mc-num{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;font-size:.83rem;color:#333;}
.cal-mc-num--link{text-decoration:none;color:#4a90d9;font-weight:600;}
.cal-mc-dots{display:flex;gap:3px;margin-top:3px;}
.cal-dot{display:inline-block;width:7px;height:7px;border-radius:50%;}
.cal-dot--blue{background:#4a90d9;}
.cal-dot--orange{background:#f5a623;}

@media(max-width:600px){
    .cal-week{grid-template-columns:repeat(7,1fr);}
    .cal-wi-title{display:none;}
    .cal-period{font-size:.8rem;}
    .cal-form-grid{grid-template-columns:1fr 1fr;}
}
</style>
<script>
function calToggleForm(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const wasOpen = el.style.display !== 'none';
    document.querySelectorAll('.cal-form').forEach(f => { f.style.display = 'none'; });
    if (!wasOpen) el.style.display = 'block';
}
</script>
@endonce
