<?php $__env->startSection('title', 'Розклад занять'); ?>
<?php $__env->startSection('content'); ?>
<h1>Розклад занять</h1>

<?php
    $current = \Carbon\Carbon::parse($date);

    $prevDate = match($mode) {
        'day'   => $current->copy()->subDay()->toDateString(),
        'week'  => $current->copy()->subWeek()->toDateString(),
        'month' => $current->copy()->subMonth()->toDateString(),
    };
    $nextDate = match($mode) {
        'day'   => $current->copy()->addDay()->toDateString(),
        'week'  => $current->copy()->addWeek()->toDateString(),
        'month' => $current->copy()->addMonth()->toDateString(),
    };

    $periodLabel = match($mode) {
        'day'   => $current->translatedFormat('l, d F Y'),
        'week'  => $current->copy()->startOfWeek()->format('d.m') . ' — ' . $current->copy()->endOfWeek()->format('d.m.Y'),
        'month' => $current->translatedFormat('F Y'),
    };
?>


<div>
    <a href="<?php echo e(route('schedule.index', ['mode' => 'day',   'date' => $date])); ?>" <?php if($mode==='day'): ?>   style="font-weight:bold" <?php endif; ?>>День</a>
    <a href="<?php echo e(route('schedule.index', ['mode' => 'week',  'date' => $date])); ?>" <?php if($mode==='week'): ?>  style="font-weight:bold" <?php endif; ?>>Тиждень</a>
    <a href="<?php echo e(route('schedule.index', ['mode' => 'month', 'date' => $date])); ?>" <?php if($mode==='month'): ?> style="font-weight:bold" <?php endif; ?>>Місяць</a>
</div>


<div style="display:flex; align-items:center; gap:12px; margin:10px 0;">
    <a href="<?php echo e(route('schedule.index', ['mode' => $mode, 'date' => $prevDate])); ?>">&larr;</a>
    <strong><?php echo e($periodLabel); ?></strong>
    <a href="<?php echo e(route('schedule.index', ['mode' => $mode, 'date' => $nextDate])); ?>">&rarr;</a>
    <a href="<?php echo e(route('schedule.index', ['mode' => $mode, 'date' => today()->toDateString()])); ?>" style="font-size:0.85em;">Сьогодні</a>
</div>


<?php if($mode === 'day'): ?>
    <?php $__empty_1 = true; $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div style="border:1px solid #ddd; padding:8px; margin:6px 0;">
        <strong><?php echo e($lesson->start_time); ?> — <?php echo e($lesson->end_time); ?></strong>
        <?php echo e($lesson->course->title); ?>

        <?php echo e($lesson->title ? "· {$lesson->title}" : ''); ?>

        <span style="color:#888;">[<?php echo e($lesson->mode === 'online' ? 'Онлайн' : 'Офлайн'); ?>]</span>
        <?php if($lesson->location): ?> · <?php echo e($lesson->location->name); ?> <?php endif; ?>
        <?php if($lesson->classroom): ?> (<?php echo e($lesson->classroom->name); ?>) <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p>Немає занять на цей день.</p>
    <?php endif; ?>


<?php elseif($mode === 'week'): ?>
    <?php $grouped = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d')); ?>
    <?php $weekStart = $current->copy()->startOfWeek(); ?>
    <?php for($d = $weekStart->copy(); $d <= $weekStart->copy()->endOfWeek(); $d->addDay()): ?>
    <?php $key = $d->format('Y-m-d'); $dayLessons = $grouped->get($key, collect()); ?>
    <div style="margin-bottom:10px;">
        <div style="margin-bottom:4px;">
            <a href="<?php echo e(route('schedule.index', ['mode' => 'day', 'date' => $key])); ?>"
               style="font-weight:<?php echo e($key === today()->toDateString() ? 'bold' : 'normal'); ?>;">
                <?php echo e($d->translatedFormat('D d.m')); ?>

            </a>
        </div>
        <?php $__empty_1 = true; $__currentLoopData = $dayLessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="padding:4px 0; border-bottom:1px solid #eee;">
            <?php echo e($lesson->start_time); ?> — <?php echo e($lesson->end_time); ?>

            · <strong><?php echo e($lesson->course->title); ?></strong>
            <?php echo e($lesson->title ? "· {$lesson->title}" : ''); ?>

            <?php if($lesson->mode === 'offline' && $lesson->location): ?> · <?php echo e($lesson->location->name); ?> <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <span style="color:#aaa; font-size:0.85em;">Немає занять</span>
        <?php endif; ?>
    </div>
    <?php endfor; ?>


<?php elseif($mode === 'month'): ?>
    <?php
        $monthStart = $current->copy()->startOfMonth();
        $monthEnd   = $current->copy()->endOfMonth();
        $grouped    = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d'));
        $cell       = $monthStart->copy()->startOfWeek();
    ?>
    <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <thead>
            <tr>
                <?php $__currentLoopData = ['ПН','ВТ','СР','ЧТ','ПТ','СБ','НД']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th style="padding:6px; border:1px solid #ddd; text-align:center;"><?php echo e($day); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
        <?php while($cell <= $monthEnd->copy()->endOfWeek()): ?>
        <tr>
            <?php for($i = 0; $i < 7; $i++): ?>
            <?php
                $key = $cell->format('Y-m-d');
                $count = $grouped->get($key, collect())->count();
                $isCurrentMonth = $cell->month === $monthStart->month;
                $isToday = $key === today()->toDateString();
            ?>
            <td style="padding:6px; border:1px solid #ddd; vertical-align:top; height:50px;
                       <?php echo e(!$isCurrentMonth ? 'color:#ccc;' : ''); ?>">
                <?php if($count > 0): ?>
                    <a href="<?php echo e(route('schedule.index', ['mode' => 'day', 'date' => $key])); ?>"
                       style="display:inline-flex; align-items:center; justify-content:center;
                              width:26px; height:26px; border-radius:50%; background:#4a90d9;
                              color:#fff; text-decoration:none; font-weight:bold; font-size:0.85em;"
                       title="<?php echo e($count); ?> <?php echo e(trans_choice('заняття|заняття|занять', $count)); ?>">
                        <?php echo e($cell->day); ?>

                    </a>
                    <span style="font-size:0.75em; color:#888;">×<?php echo e($count); ?></span>
                <?php else: ?>
                    <span style="<?php echo e($isToday ? 'font-weight:bold;' : ''); ?>"><?php echo e($cell->day); ?></span>
                <?php endif; ?>
            </td>
            <?php $cell->addDay(); ?>
            <?php endfor; ?>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>


<?php if(auth()->user()->isTeacher() || auth()->user()->isAdmin()): ?>
<hr>
<h2>Додати заняття</h2>
<form method="POST" action="<?php echo e(route('teacher.schedule.store')); ?>">
    <?php echo csrf_field(); ?>
    <select name="course_id" required>
        <option value="">Оберіть курс</option>
        <?php $__currentLoopData = auth()->user()->isTeacher() ? auth()->user()->taughtCourses : \App\Models\Course::active()->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($c->id); ?>"><?php echo e($c->title); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <input type="text" name="title" placeholder="Тема заняття">
    <select name="mode">
        <option value="offline">Офлайн</option>
        <option value="online">Онлайн</option>
    </select>
    <select name="location_id">
        <option value="">Локація</option>
        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->name); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="classroom_id">
        <option value="">Аудиторія</option>
        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $__currentLoopData = $loc->classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($room->id); ?>"><?php echo e($loc->name); ?> — <?php echo e($room->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <input type="date" name="date" value="<?php echo e($mode === 'day' ? $date : ''); ?>" required>
    <input type="time" name="start_time" required>
    <input type="time" name="end_time" required>
    <button type="submit">Додати заняття</button>
</form>
<?php endif; ?>


<?php $__currentLoopData = $lessons->where('attendance_confirmed', false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(auth()->user()->isTeacher() && $lesson->teacher_id === auth()->id() && $lesson->date <= today()): ?>
    <div>
        <h3>Присутність: <?php echo e($lesson->course->title); ?> (<?php echo e($lesson->date->format('d.m')); ?>)</h3>
        <form method="POST" action="<?php echo e(route('teacher.schedule.attendance', $lesson)); ?>">
            <?php echo csrf_field(); ?>
            <?php $__currentLoopData = $lesson->course->activeStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <label>
                    <input type="checkbox" name="present_students[]" value="<?php echo e($student->id); ?>" checked>
                    <?php echo e($student->full_name); ?>

                </label>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <button type="submit">Підтвердити присутність</button>
        </form>
    </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/schedule/index.blade.php ENDPATH**/ ?>