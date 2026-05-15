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
                 onclick="openLessonModal(this)"
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
                <div class="cal-item-time">🎂</div>
                <div class="cal-item-body">
                    <strong>{{ $bu->full_name }}</strong>
                    <span class="cal-badge-ev cal-badge-ev--birthday">День народження</span>
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
                <form method="POST" action="{{ route('teacher.events.destroy', $e) }}" onsubmit="return confirm('Видалити подію?')" style="margin-left:auto;" class="form-inline">
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
            <div class="cal-week-item cal-week-item--lesson" onclick="openLessonModal(this)"
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
            <div class="cal-week-item cal-week-item--event" style="border-left:3px solid {{ $ec }};">
                <div class="cal-wi-time">{{ $e->start_time ? substr($e->start_time,0,5) : '' }}</div>
                <div class="cal-wi-title">{{ $e->title }}</div>
            </div>
            @endforeach
            @foreach($db as $b)
            <div class="cal-week-item cal-week-item--birthday">
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
{{-- ── Lesson detail modal ── --}}
<div class="lm-overlay" id="lm-overlay" onclick="if(event.target===this)closeLessonModal()">
<div class="lm-card" id="lm-card">
    <div class="lm-handle"></div>
    <p class="lm-title" id="lm-title"></p>
    <p class="lm-sub" id="lm-sub"></p>
    <div class="lm-row" id="lm-meta"></div>
    <div id="lm-status-row" class="lm-status-row"></div>

    <div id="lm-actions-section">
        <hr class="lm-divider">

        {{-- Cancel tab button --}}
        <div class="lm-tab-row">
            <button type="button" class="lm-btn lm-btn--cancel" onclick="lmShowSection('cancel')" id="lm-tab-cancel">Скасувати заняття</button>
            <button type="button" class="lm-btn lm-btn--reschedule" onclick="lmShowSection('reschedule')" id="lm-tab-reschedule">Перенести заняття</button>
            <button type="button" class="lm-btn lm-btn--danger" onclick="lmShowSection('delete')" id="lm-tab-delete">Видалити заняття</button>
            <button type="button" class="lm-btn lm-btn--ghost" onclick="closeLessonModal()">Закрити</button>
        </div>

        {{-- Cancel form --}}
        <div id="lm-sec-cancel" style="display:none;">
            <p class="lm-section-title lm-section-title--cancel">Скасування заняття</p>
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
            <p class="lm-section-title lm-section-title--reschedule">Перенесення заняття</p>
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

        {{-- Delete form --}}
        <div id="lm-sec-delete" style="display:none;">
            <p class="lm-section-title lm-section-title--danger">Видалення заняття</p>
            <p style="color:#d32f2f; margin:10px 0;">Ця дія не може бути скасована. Видаліть заняття, тільки якщо це помилка.</p>
            <form id="lm-form-delete" method="POST">
                @csrf @method('DELETE')
                <div class="lm-actions">
                    <button type="submit" class="lm-btn lm-btn--danger" onclick="return confirm('Ви впевнені? Це видалить заняття назавжди.')">Видалити безповоротно</button>
                    <button type="button" class="lm-btn lm-btn--ghost" onclick="lmShowSection(null)">Скасувати</button>
                </div>
            </form>
        </div>
    </div>

    <div id="lm-close-only" class="lm-close-only" style="display:none;">
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
        const note = d.note ? `<div class="lm-note">${d.note}</div>` : '';
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
        document.getElementById('lm-form-delete').action     = base;

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
    ['cancel','reschedule','delete'].forEach(s => {
        document.getElementById('lm-sec-' + s).style.display = (s === name) ? 'block' : 'none';
    });
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLessonModal(); });
</script>
@endonce
