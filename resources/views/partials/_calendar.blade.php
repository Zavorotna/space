{{--
  Variables:
    $schedDate      Carbon
    $schedMode      string (day|week|month)
    $schedLessons   Collection<Lesson>
    $schedEvents    Collection<CalendarEvent>
    $schedLocations Collection<Location>   (optional, for add form)
    $schedCourses   Collection<Course>     (optional, for add form)
    $schedBirthdays Collection            (optional, grouped by date)
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

    $lessonsByDate   = $schedLessons->groupBy(fn($l) => $l->date->format('Y-m-d'));
    $eventsByDate    = $schedEvents->groupBy(fn($e) => $e->date->format('Y-m-d'));
    $birthdaysByDate = $schedBirthdays ?? collect();

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
        $dayKey        = $cur->toDateString();
        $dayLessons    = $lessonsByDate->get($dayKey, collect());
        $dayEvents     = $eventsByDate->get($dayKey, collect());
        $dayBirthdays  = $birthdaysByDate->get($dayKey, collect());
        $allItems      = $dayLessons->map(fn($l) => ['kind'=>'lesson','time'=>$l->start_time,'obj'=>$l])
                            ->merge($dayEvents->map(fn($e) => ['kind'=>'event','time'=>$e->start_time ?? '00:00','obj'=>$e]))
                            ->merge($dayBirthdays->map(fn($b) => ['kind'=>'birthday','time'=>'00:00','obj'=>$b]))
                            ->sortBy('time');
    @endphp
    <div class="cal-day">
        @forelse($allItems as $item)
            @if($item['kind'] === 'lesson')
            @php
                $l = $item['obj'];
                $lEndsAt  = \Carbon\Carbon::parse($l->date->format('Y-m-d') . ' ' . $l->end_time);
                $lCanAct  = !empty($canEdit) && $lEndsAt->isFuture() && !$l->completion_status;
                $lStatus  = $l->completion_status ?? '';
                $lStatusLabels = ['full'=>'✅ Повне','partial'=>'⚡ Часткове','cancelled'=>'❌ Скасовано','rescheduled'=>'🔄 Перенесено'];
            @endphp
            <div class="cal-item cal-item--lesson {{ $lStatus ? 'cal-item--done' : '' }}"
                 onclick="openLessonModal(this)" style="cursor:pointer;"
                 data-lid="{{ $l->id }}"
                 data-title="{{ e($l->course->title) }}"
                 data-sub="{{ e($l->title ?? '') }}"
                 data-date-fmt="{{ $l->date->translatedFormat('d F Y') }}"
                 data-date-raw="{{ $l->date->format('Y-m-d') }}"
                 data-start="{{ substr($l->start_time,0,5) }}"
                 data-end="{{ substr($l->end_time,0,5) }}"
                 data-mode="{{ $l->mode === 'online' ? 'Онлайн' : 'Офлайн' }}"
                 data-loc="{{ e($l->location?->name ?? '') }}"
                 data-room="{{ e($l->classroom?->name ?? '') }}"
                 data-teacher="{{ e($l->teacher?->full_name ?? '') }}"
                 data-status="{{ $lStatus }}"
                 data-status-label="{{ $lStatusLabels[$lStatus] ?? '' }}"
                 data-note="{{ e($l->completion_note ?? '') }}"
                 data-can-act="{{ $lCanAct ? '1' : '0' }}">
                <div class="cal-item-time">{{ substr($l->start_time,0,5) }}<br>{{ substr($l->end_time,0,5) }}</div>
                <div class="cal-item-body">
                    <strong>{{ $l->course->title }}</strong>
                    @if($l->title) <span class="cal-sub"> · {{ $l->title }}</span> @endif
                    <div class="cal-meta">
                        <span class="cal-badge-mode">{{ $l->mode === 'online' ? 'Онлайн' : 'Офлайн' }}</span>
                        @if($l->location) {{ $l->location->name }} @endif
                        @if($l->classroom) ({{ $l->classroom->name }}) @endif
                        @isset($l->teacher) · {{ $l->teacher->full_name }} @endisset
                        @if($lStatus) <span style="margin-left:4px;">{{ $lStatusLabels[$lStatus] ?? '' }}</span> @endif
                    </div>
                </div>
            </div>
            @elseif($item['kind'] === 'birthday')
            @php $b = $item['obj']; $bu = $b['user']; @endphp
            <div class="cal-item cal-item--birthday">
                <div class="cal-item-time" style="font-size:1.1rem;">🎂</div>
                <div class="cal-item-body">
                    <strong>{{ $bu->full_name }}</strong>
                    <span class="cal-badge-ev" style="background:#e84393;">День народження</span>
                    @if($bu->birthday)
                    <div class="cal-meta">{{ $bu->birthday->format('d.m.Y') }}</div>
                    @endif
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
            $db  = $birthdaysByDate->get($key, collect());
            $isT = $key === $today;
        @endphp
        <div class="cal-week-col {{ $isT ? 'cal-week-col--today' : '' }}">
            <a href="{{ route('dashboard', ['schedule_mode'=>'day','schedule_date'=>$key]) }}" class="cal-week-head">
                <span class="cal-week-dname">{{ $d->translatedFormat('D') }}</span>
                <span class="cal-week-num {{ $isT ? 'cal-week-num--today' : '' }}">{{ $d->day }}</span>
            </a>
            @foreach($dl as $l)
            @php
                $wEndsAt = \Carbon\Carbon::parse($l->date->format('Y-m-d') . ' ' . $l->end_time);
                $wCanAct = !empty($canEdit) && $wEndsAt->isFuture() && !$l->completion_status;
                $wStatus = $l->completion_status ?? '';
                $wStatusLabels = ['full'=>'✅','partial'=>'⚡','cancelled'=>'❌','rescheduled'=>'🔄'];
            @endphp
            <div class="cal-week-item cal-week-item--lesson" onclick="openLessonModal(this)" style="cursor:pointer;"
                 data-lid="{{ $l->id }}"
                 data-title="{{ e($l->course->title) }}"
                 data-sub="{{ e($l->title ?? '') }}"
                 data-date-fmt="{{ $l->date->translatedFormat('d F Y') }}"
                 data-date-raw="{{ $l->date->format('Y-m-d') }}"
                 data-start="{{ substr($l->start_time,0,5) }}"
                 data-end="{{ substr($l->end_time,0,5) }}"
                 data-mode="{{ $l->mode === 'online' ? 'Онлайн' : 'Офлайн' }}"
                 data-loc="{{ e($l->location?->name ?? '') }}"
                 data-room="{{ e($l->classroom?->name ?? '') }}"
                 data-teacher="{{ e($l->teacher?->full_name ?? '') }}"
                 data-status="{{ $wStatus }}"
                 data-status-label="{{ $wStatusLabels[$wStatus] ?? '' }}"
                 data-note="{{ e($l->completion_note ?? '') }}"
                 data-can-act="{{ $wCanAct ? '1' : '0' }}">
                <div class="cal-wi-time">{{ substr($l->start_time,0,5) }} {{ $wStatus ? ($wStatusLabels[$wStatus] ?? '') : '' }}</div>
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
            @foreach($db as $b)
            <div class="cal-week-item" style="border-left:3px solid #e84393; background:#fff0f7;">
                <div class="cal-wi-time">🎂</div>
                <div class="cal-wi-title">{{ $b['user']->full_name }}</div>
            </div>
            @endforeach
            @if($dl->isEmpty() && $de->isEmpty() && $db->isEmpty())
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
            $bCnt   = $birthdaysByDate->get($key, collect())->count();
            $inMon  = $cell->month === $mStart->month;
            $isT    = $key === $today;
        @endphp
        <div class="cal-month-cell {{ !$inMon ? 'cal-mc--out' : '' }} {{ $isT ? 'cal-mc--today' : '' }}">
            @if($lCnt || $eCnt || $bCnt)
            <a href="{{ route('dashboard', ['schedule_mode'=>'day','schedule_date'=>$key]) }}" class="cal-mc-num cal-mc-num--link">{{ $cell->day }}</a>
            @else
            <span class="cal-mc-num">{{ $cell->day }}</span>
            @endif
            <div class="cal-mc-dots">
                @if($lCnt) <span class="cal-dot cal-dot--blue" title="{{ $lCnt }} занять"></span> @endif
                @if($eCnt) <span class="cal-dot cal-dot--orange" title="{{ $eCnt }} подій"></span> @endif
                @if($bCnt) <span class="cal-dot cal-dot--pink" title="{{ $bCnt }} днів народження"></span> @endif
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
.cal-dot--pink{background:#e84393;}
.cal-item--birthday{border-left-color:#e84393;background:#fff0f7;}
.cal-item--done{opacity:.75;}

/* Lesson modal */
.lm-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:flex-end;justify-content:center;}
.lm-overlay.lm-open{display:flex;}
.lm-card{background:#fff;border-radius:14px 14px 0 0;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;padding:20px 20px 32px;box-shadow:0 -4px 24px rgba(0,0,0,.15);}
.lm-handle{width:40px;height:4px;background:#ddd;border-radius:2px;margin:0 auto 16px;}
.lm-title{font-size:1.05rem;font-weight:700;margin:0 0 2px;}
.lm-sub{color:#888;font-size:.85rem;margin:0 0 12px;}
.lm-row{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:6px;font-size:.88rem;}
.lm-badge{padding:2px 8px;border-radius:4px;background:#f0f0f0;color:#555;font-size:.78rem;}
.lm-status{padding:3px 10px;border-radius:4px;font-size:.82rem;font-weight:600;}
.lm-status--cancelled{background:#fdecea;color:#c0392b;}
.lm-status--full{background:#eafaf1;color:#1e8449;}
.lm-status--partial{background:#fef9e7;color:#b7950b;}
.lm-status--rescheduled{background:#eaf4fb;color:#1a6fa8;}
.lm-divider{border:none;border-top:1px solid #eee;margin:14px 0;}
.lm-section-title{font-weight:600;font-size:.9rem;margin:0 0 8px;}
.lm-field{margin-bottom:10px;}
.lm-field label{display:block;font-size:.78rem;color:#888;margin-bottom:3px;}
.lm-field input,.lm-field textarea{width:100%;padding:7px 9px;border:1px solid #ddd;border-radius:5px;font-size:.88rem;}
.lm-field textarea{resize:vertical;}
.lm-row-time{display:flex;gap:10px;}
.lm-row-time .lm-field{flex:1;}
.lm-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;}
.lm-btn{padding:8px 16px;border:none;border-radius:6px;cursor:pointer;font-size:.88rem;font-weight:500;}
.lm-btn--cancel{background:#e74c3c;color:#fff;}
.lm-btn--reschedule{background:#4a90d9;color:#fff;}
.lm-btn--ghost{background:#e8e8e8;color:#555;}

@media(max-width:600px){
    .cal-week{grid-template-columns:repeat(7,1fr);}
    .cal-wi-title{display:none;}
    .cal-period{font-size:.8rem;}
    .cal-form-grid{grid-template-columns:1fr 1fr;}
}
</style>

{{-- ── Lesson detail modal ── --}}
<div class="lm-overlay" id="lm-overlay" onclick="if(event.target===this)closeLessonModal()">
<div class="lm-card" id="lm-card">
    <div class="lm-handle"></div>
    <p class="lm-title" id="lm-title"></p>
    <p class="lm-sub" id="lm-sub"></p>
    <div class="lm-row" id="lm-meta"></div>
    <div id="lm-status-row" style="margin-bottom:10px;"></div>

    <div id="lm-actions-section">
        <hr class="lm-divider">

        {{-- Cancel tab button --}}
        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <button type="button" class="lm-btn lm-btn--cancel" onclick="lmShowSection('cancel')" id="lm-tab-cancel">Скасувати заняття</button>
            <button type="button" class="lm-btn lm-btn--reschedule" onclick="lmShowSection('reschedule')" id="lm-tab-reschedule">Перенести заняття</button>
            <button type="button" class="lm-btn lm-btn--ghost" onclick="closeLessonModal()">Закрити</button>
        </div>

        {{-- Cancel form --}}
        <div id="lm-sec-cancel" style="display:none;">
            <p class="lm-section-title" style="color:#c0392b;">Скасування заняття</p>
            <form id="lm-form-cancel" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="lm-field">
                    <label>Причина скасування *</label>
                    <textarea name="reason" rows="3" required placeholder="Вкажіть причину..."></textarea>
                </div>
                <div class="lm-actions">
                    <button type="submit" class="lm-btn lm-btn--cancel">Підтвердити скасування</button>
                    <button type="button" class="lm-btn lm-btn--ghost" onclick="lmShowSection(null)">Назад</button>
                </div>
            </form>
        </div>

        {{-- Reschedule form --}}
        <div id="lm-sec-reschedule" style="display:none;">
            <p class="lm-section-title" style="color:#1a6fa8;">Перенесення заняття</p>
            <form id="lm-form-reschedule" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="lm-field">
                    <label>Нова дата *</label>
                    <input type="date" name="new_date" required id="lm-new-date">
                </div>
                <div class="lm-row-time">
                    <div class="lm-field"><label>Початок *</label><input type="time" name="new_start_time" required id="lm-new-start"></div>
                    <div class="lm-field"><label>Кінець *</label><input type="time" name="new_end_time" required id="lm-new-end"></div>
                </div>
                <div class="lm-field">
                    <label>Причина перенесення *</label>
                    <textarea name="reason" rows="3" required placeholder="Вкажіть причину..."></textarea>
                </div>
                <div class="lm-actions">
                    <button type="submit" class="lm-btn lm-btn--reschedule">Підтвердити перенесення</button>
                    <button type="button" class="lm-btn lm-btn--ghost" onclick="lmShowSection(null)">Назад</button>
                </div>
            </form>
        </div>
    </div>

    <div id="lm-close-only" style="display:none;margin-top:12px;">
        <button type="button" class="lm-btn lm-btn--ghost" onclick="closeLessonModal()">Закрити</button>
    </div>
</div>
</div>

<script>
function calToggleForm(id) {
    const el = document.getElementById(id);
    if (!el) return;
    const wasOpen = el.style.display !== 'none';
    document.querySelectorAll('.cal-form').forEach(f => { f.style.display = 'none'; });
    if (!wasOpen) el.style.display = 'block';
}

function openLessonModal(el) {
    const d = el.dataset;
    const lid = d.lid;
    const statusColors = {cancelled:'lm-status--cancelled',full:'lm-status--full',partial:'lm-status--partial',rescheduled:'lm-status--rescheduled'};

    document.getElementById('lm-title').textContent = d.title;
    document.getElementById('lm-sub').textContent   = [d.dateFmt, d.start + '–' + d.end, d.sub].filter(Boolean).join('  ·  ');

    const meta = [d.mode, d.loc, d.room, d.teacher].filter(Boolean)
        .map(t => `<span class="lm-badge">${t}</span>`).join('');
    document.getElementById('lm-meta').innerHTML = meta;

    const statusRow = document.getElementById('lm-status-row');
    if (d.status) {
        const cls = statusColors[d.status] || '';
        const note = d.note ? `<div style="font-size:.82rem;color:#888;margin-top:4px;">${d.note}</div>` : '';
        statusRow.innerHTML = `<span class="lm-status ${cls}">${d.statusLabel}</span>${note}`;
    } else {
        statusRow.innerHTML = '';
    }

    const canAct = d.canAct === '1';
    document.getElementById('lm-actions-section').style.display = canAct ? 'block' : 'none';
    document.getElementById('lm-close-only').style.display      = canAct ? 'none'  : 'block';

    if (canAct) {
        const base = '{{ url("/teacher/schedule") }}/' + lid;
        document.getElementById('lm-form-cancel').action     = base + '/cancel';
        document.getElementById('lm-form-reschedule').action = base + '/reschedule';

        const today = new Date().toISOString().slice(0,10);
        document.getElementById('lm-new-date').min   = today;
        document.getElementById('lm-new-date').value = d.dateRaw;
        document.getElementById('lm-new-start').value = d.start;
        document.getElementById('lm-new-end').value   = d.end;

        lmShowSection(null);
    }

    document.getElementById('lm-overlay').classList.add('lm-open');
    document.body.style.overflow = 'hidden';
}

function closeLessonModal() {
    document.getElementById('lm-overlay').classList.remove('lm-open');
    document.body.style.overflow = '';
}

function lmShowSection(name) {
    ['cancel','reschedule'].forEach(s => {
        document.getElementById('lm-sec-' + s).style.display = (s === name) ? 'block' : 'none';
    });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLessonModal(); });
</script>
@endonce
