
<?php
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
?>

<div class="cal-wrap">

    
    <div class="cal-header">
        <div class="cal-tabs">
            <?php $__currentLoopData = ['day' => 'День', 'week' => 'Тиждень', 'month' => 'Місяць']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('dashboard', ['schedule_mode' => $m, 'schedule_date' => $cur->toDateString()])); ?>"
               class="cal-tab <?php echo e($schedMode === $m ? 'cal-tab--active' : ''); ?>"><?php echo e($label); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="cal-nav">
            <a href="<?php echo e(route('dashboard', ['schedule_mode' => $schedMode, 'schedule_date' => $prevDate])); ?>" class="cal-arrow">&#8249;</a>
            <span class="cal-period"><?php echo e($periodLabel); ?></span>
            <a href="<?php echo e(route('dashboard', ['schedule_mode' => $schedMode, 'schedule_date' => $nextDate])); ?>" class="cal-arrow">&#8250;</a>
            <?php if($cur->toDateString() !== $today): ?>
            <a href="<?php echo e(route('dashboard', ['schedule_mode' => $schedMode, 'schedule_date' => $today])); ?>" class="cal-today-link">Сьогодні</a>
            <?php endif; ?>
        </div>

        <?php if(!empty($canEdit)): ?>
        <div class="cal-actions">
            <button type="button" onclick="calToggleForm('cal-lesson-form')" class="cal-btn cal-btn--blue">+ Заняття</button>
            <button type="button" onclick="calToggleForm('cal-event-form')"  class="cal-btn cal-btn--orange">+ Подія</button>
        </div>
        <?php endif; ?>
    </div>

    
    <?php if(!empty($canEdit)): ?>
    <div id="cal-lesson-form" class="cal-form" style="display:none;">
        <p class="cal-form-title">Нове заняття</p>
        <form method="POST" action="<?php echo e(route('teacher.schedule.store')); ?>">
            <?php echo csrf_field(); ?>
            <div class="cal-form-grid">
                <div class="cal-field">
                    <label>Курс *</label>
                    <select name="course_id" required>
                        <option value="">Оберіть курс</option>
                        <?php $__currentLoopData = $schedCourses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__currentLoopData = $schedLocations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="cal-field">
                    <label>Аудиторія</label>
                    <select name="classroom_id">
                        <option value="">—</option>
                        <?php $__currentLoopData = $schedLocations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $__currentLoopData = $loc->classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($room->id); ?>"><?php echo e($loc->name); ?> — <?php echo e($room->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="cal-field">
                    <label>Дата *</label>
                    <input type="date" name="date" value="<?php echo e($defaultDate); ?>" required>
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

    
    <div id="cal-event-form" class="cal-form" style="display:none;">
        <p class="cal-form-title">Нова подія</p>
        <form method="POST" action="<?php echo e(route('teacher.events.store')); ?>">
            <?php echo csrf_field(); ?>
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
                    <input type="date" name="date" value="<?php echo e($defaultDate); ?>" required>
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
    <?php endif; ?>

    
    <?php if($schedMode === 'day'): ?>
    <?php
        $dayKey        = $cur->toDateString();
        $dayLessons    = $lessonsByDate->get($dayKey, collect());
        $dayEvents     = $eventsByDate->get($dayKey, collect());
        $dayBirthdays  = $birthdaysByDate->get($dayKey, collect());
        $allItems      = $dayLessons->map(fn($l) => ['kind'=>'lesson','time'=>$l->start_time,'obj'=>$l])
                            ->merge($dayEvents->map(fn($e) => ['kind'=>'event','time'=>$e->start_time ?? '00:00','obj'=>$e]))
                            ->merge($dayBirthdays->map(fn($b) => ['kind'=>'birthday','time'=>'00:00','obj'=>$b]))
                            ->sortBy('time');
    ?>
    <div class="cal-day">
        <?php $__empty_1 = true; $__currentLoopData = $allItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if($item['kind'] === 'lesson'): ?>
            <?php
                $l = $item['obj'];
                $lEndsAt  = \Carbon\Carbon::parse($l->date->format('Y-m-d') . ' ' . $l->end_time);
                $lCanAct  = !empty($canEdit) && $lEndsAt->isFuture() && !$l->completion_status;
                $lStatus  = $l->completion_status ?? '';
                $lStatusLabels = ['full'=>'✅ Повне','partial'=>'⚡ Часткове','cancelled'=>'❌ Скасовано','rescheduled'=>'🔄 Перенесено'];
            ?>
            <div class="cal-item cal-item--lesson <?php echo e($lStatus ? 'cal-item--done' : ''); ?>"
                 onclick="openLessonModal(this)"
                 data-lid="<?php echo e($l->id); ?>"
                 data-title="<?php echo e(e($l->course->title)); ?>"
                 data-sub="<?php echo e(e($l->title ?? '')); ?>"
                 data-date-fmt="<?php echo e($l->date->translatedFormat('d F Y')); ?>"
                 data-date-raw="<?php echo e($l->date->format('Y-m-d')); ?>"
                 data-start="<?php echo e(substr($l->start_time,0,5)); ?>"
                 data-end="<?php echo e(substr($l->end_time,0,5)); ?>"
                 data-mode="<?php echo e($l->mode === 'online' ? 'Онлайн' : 'Офлайн'); ?>"
                 data-loc="<?php echo e(e($l->location?->name ?? '')); ?>"
                 data-room="<?php echo e(e($l->classroom?->name ?? '')); ?>"
                 data-teacher="<?php echo e(e($l->teacher?->full_name ?? '')); ?>"
                 data-status="<?php echo e($lStatus); ?>"
                 data-status-label="<?php echo e($lStatusLabels[$lStatus] ?? ''); ?>"
                 data-note="<?php echo e(e($l->completion_note ?? '')); ?>"
                 data-can-act="<?php echo e($lCanAct ? '1' : '0'); ?>">
                <div class="cal-item-time"><?php echo e(substr($l->start_time,0,5)); ?><br><?php echo e(substr($l->end_time,0,5)); ?></div>
                <div class="cal-item-body">
                    <strong><?php echo e($l->course->title); ?></strong>
                    <?php if($l->title): ?> <span class="cal-sub"> · <?php echo e($l->title); ?></span> <?php endif; ?>
                    <div class="cal-meta">
                        <span class="cal-badge-mode"><?php echo e($l->mode === 'online' ? 'Онлайн' : 'Офлайн'); ?></span>
                        <?php if($l->location): ?> <?php echo e($l->location->name); ?> <?php endif; ?>
                        <?php if($l->classroom): ?> (<?php echo e($l->classroom->name); ?>) <?php endif; ?>
                        <?php if(isset($l->teacher)): ?> · <?php echo e($l->teacher->full_name); ?> <?php endif; ?>
                        <?php if($lStatus): ?> <span style="margin-left:4px;"><?php echo e($lStatusLabels[$lStatus] ?? ''); ?></span> <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php elseif($item['kind'] === 'birthday'): ?>
            <?php $b = $item['obj']; $bu = $b['user']; ?>
            <div class="cal-item cal-item--birthday">
                <div class="cal-item-time">🎂</div>
                <div class="cal-item-body">
                    <strong><?php echo e($bu->full_name); ?></strong>
                    <span class="cal-badge-ev cal-badge-ev--birthday">День народження</span>
                    <?php if($bu->birthday): ?>
                    <div class="cal-meta"><?php echo e($bu->birthday->format('d.m.Y')); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <?php $e = $item['obj']; $ec = $evColors[$e->type] ?? '#7f8c8d'; ?>
            <div class="cal-item" style="border-left-color:<?php echo e($ec); ?>;">
                <div class="cal-item-time">
                    <?php echo e($e->start_time ? substr($e->start_time,0,5) : '—'); ?>

                    <?php if($e->end_time): ?><br><?php echo e(substr($e->end_time,0,5)); ?><?php endif; ?>
                </div>
                <div class="cal-item-body">
                    <strong><?php echo e($e->title); ?></strong>
                    <span class="cal-badge-ev" style="background:<?php echo e($ec); ?>;"><?php echo e($evLabels[$e->type] ?? 'Подія'); ?></span>
                    <?php if($e->description): ?> <div class="cal-meta"><?php echo e($e->description); ?></div> <?php endif; ?>
                </div>
                <?php if(!empty($canEdit)): ?>
                <form method="POST" action="<?php echo e(route('teacher.events.destroy', $e)); ?>" onsubmit="return confirm('Видалити подію?')" style="margin-left:auto;" class="form-inline">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="cal-del-btn">✕</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="cal-empty">Немає занять та подій.</p>
        <?php endif; ?>
    </div>

    
    <?php elseif($schedMode === 'week'): ?>
    <?php $weekStart = $cur->copy()->startOfWeek(); ?>
    <div class="cal-week">
        <?php for($d = $weekStart->copy(); $d <= $weekStart->copy()->endOfWeek(); $d->addDay()): ?>
        <?php
            $key = $d->format('Y-m-d');
            $dl  = $lessonsByDate->get($key, collect());
            $de  = $eventsByDate->get($key, collect());
            $db  = $birthdaysByDate->get($key, collect());
            $isT = $key === $today;
        ?>
        <div class="cal-week-col <?php echo e($isT ? 'cal-week-col--today' : ''); ?>">
            <a href="<?php echo e(route('dashboard', ['schedule_mode'=>'day','schedule_date'=>$key])); ?>" class="cal-week-head">
                <span class="cal-week-dname"><?php echo e($d->translatedFormat('D')); ?></span>
                <span class="cal-week-num <?php echo e($isT ? 'cal-week-num--today' : ''); ?>"><?php echo e($d->day); ?></span>
            </a>
            <?php $__currentLoopData = $dl; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $wEndsAt = \Carbon\Carbon::parse($l->date->format('Y-m-d') . ' ' . $l->end_time);
                $wCanAct = !empty($canEdit) && $wEndsAt->isFuture() && !$l->completion_status;
                $wStatus = $l->completion_status ?? '';
                $wStatusLabels = ['full'=>'✅','partial'=>'⚡','cancelled'=>'❌','rescheduled'=>'🔄'];
            ?>
            <div class="cal-week-item cal-week-item--lesson" onclick="openLessonModal(this)"
                 data-lid="<?php echo e($l->id); ?>"
                 data-title="<?php echo e(e($l->course->title)); ?>"
                 data-sub="<?php echo e(e($l->title ?? '')); ?>"
                 data-date-fmt="<?php echo e($l->date->translatedFormat('d F Y')); ?>"
                 data-date-raw="<?php echo e($l->date->format('Y-m-d')); ?>"
                 data-start="<?php echo e(substr($l->start_time,0,5)); ?>"
                 data-end="<?php echo e(substr($l->end_time,0,5)); ?>"
                 data-mode="<?php echo e($l->mode === 'online' ? 'Онлайн' : 'Офлайн'); ?>"
                 data-loc="<?php echo e(e($l->location?->name ?? '')); ?>"
                 data-room="<?php echo e(e($l->classroom?->name ?? '')); ?>"
                 data-teacher="<?php echo e(e($l->teacher?->full_name ?? '')); ?>"
                 data-status="<?php echo e($wStatus); ?>"
                 data-status-label="<?php echo e($wStatusLabels[$wStatus] ?? ''); ?>"
                 data-note="<?php echo e(e($l->completion_note ?? '')); ?>"
                 data-can-act="<?php echo e($wCanAct ? '1' : '0'); ?>">
                <div class="cal-wi-time"><?php echo e(substr($l->start_time,0,5)); ?> <?php echo e($wStatus ? ($wStatusLabels[$wStatus] ?? '') : ''); ?></div>
                <div class="cal-wi-title"><?php echo e($l->course->title); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $de; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $ec = $evColors[$e->type] ?? '#7f8c8d'; ?>
            <div class="cal-week-item cal-week-item--event" style="border-left:3px solid <?php echo e($ec); ?>;">
                <div class="cal-wi-time"><?php echo e($e->start_time ? substr($e->start_time,0,5) : ''); ?></div>
                <div class="cal-wi-title"><?php echo e($e->title); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $db; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="cal-week-item cal-week-item--birthday">
                <div class="cal-wi-time">🎂</div>
                <div class="cal-wi-title"><?php echo e($b['user']->full_name); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($dl->isEmpty() && $de->isEmpty() && $db->isEmpty()): ?>
            <div class="cal-week-empty">—</div>
            <?php endif; ?>
        </div>
        <?php endfor; ?>
    </div>

    
    <?php elseif($schedMode === 'month'): ?>
    <?php
        $mStart = $cur->copy()->startOfMonth();
        $mEnd   = $cur->copy()->endOfMonth();
        $cell   = $mStart->copy()->startOfWeek();
    ?>
    <div class="cal-month-wrap">
        <div class="cal-month-head">
            <?php $__currentLoopData = ['ПН','ВТ','СР','ЧТ','ПТ','СБ','НД']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div><?php echo e($dn); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="cal-month-grid">
        <?php while($cell <= $mEnd->copy()->endOfWeek()): ?>
        <?php
            $key    = $cell->format('Y-m-d');
            $lCnt   = $lessonsByDate->get($key, collect())->count();
            $eCnt   = $eventsByDate->get($key, collect())->count();
            $bCnt   = $birthdaysByDate->get($key, collect())->count();
            $inMon  = $cell->month === $mStart->month;
            $isT    = $key === $today;
        ?>
        <div class="cal-month-cell <?php echo e(!$inMon ? 'cal-mc--out' : ''); ?> <?php echo e($isT ? 'cal-mc--today' : ''); ?>">
            <?php if($lCnt || $eCnt || $bCnt): ?>
            <a href="<?php echo e(route('dashboard', ['schedule_mode'=>'day','schedule_date'=>$key])); ?>" class="cal-mc-num cal-mc-num--link"><?php echo e($cell->day); ?></a>
            <?php else: ?>
            <span class="cal-mc-num"><?php echo e($cell->day); ?></span>
            <?php endif; ?>
            <div class="cal-mc-dots">
                <?php if($lCnt): ?> <span class="cal-dot cal-dot--blue" title="<?php echo e($lCnt); ?> занять"></span> <?php endif; ?>
                <?php if($eCnt): ?> <span class="cal-dot cal-dot--orange" title="<?php echo e($eCnt); ?> подій"></span> <?php endif; ?>
                <?php if($bCnt): ?> <span class="cal-dot cal-dot--pink" title="<?php echo e($bCnt); ?> днів народження"></span> <?php endif; ?>
            </div>
        </div>
        <?php $cell->addDay(); ?>
        <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php if (! $__env->hasRenderedOnce('7902b2be-1c3e-4f55-b016-226e906ffce3')): $__env->markAsRenderedOnce('7902b2be-1c3e-4f55-b016-226e906ffce3'); ?>

<div class="lm-overlay" id="lm-overlay" onclick="if(event.target===this)closeLessonModal()">
<div class="lm-card" id="lm-card">
    <div class="lm-handle"></div>
    <p class="lm-title" id="lm-title"></p>
    <p class="lm-sub" id="lm-sub"></p>
    <div class="lm-row" id="lm-meta"></div>
    <div id="lm-status-row" class="lm-status-row"></div>

    <div id="lm-actions-section">
        <hr class="lm-divider">

        
        <div class="lm-tab-row">
            <button type="button" class="lm-btn lm-btn--cancel" onclick="lmShowSection('cancel')" id="lm-tab-cancel">Скасувати заняття</button>
            <button type="button" class="lm-btn lm-btn--reschedule" onclick="lmShowSection('reschedule')" id="lm-tab-reschedule">Перенести заняття</button>
            <button type="button" class="lm-btn lm-btn--ghost" onclick="closeLessonModal()">Закрити</button>
        </div>

        
        <div id="lm-sec-cancel" style="display:none;">
            <p class="lm-section-title lm-section-title--cancel">Скасування заняття</p>
            <form id="lm-form-cancel" method="POST">
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
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

        
        <div id="lm-sec-reschedule" style="display:none;">
            <p class="lm-section-title lm-section-title--reschedule">Перенесення заняття</p>
            <form id="lm-form-reschedule" method="POST">
                <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
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
        const base = '<?php echo e(url("/teacher/schedule")); ?>/' + lid;
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
<?php endif; ?>
<?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/partials/_calendar.blade.php ENDPATH**/ ?>