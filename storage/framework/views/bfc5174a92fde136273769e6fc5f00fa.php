<?php $__env->startSection('title', 'Розклад занять'); ?>
<?php $__env->startSection('content'); ?>
<h1>Розклад занять</h1>

<div>
    <a href="<?php echo e(route('schedule.index', ['mode' => 'day', 'date' => $date])); ?>" <?php if($mode==='day'): ?> style="font-weight:bold" <?php endif; ?>>день</a>
    <a href="<?php echo e(route('schedule.index', ['mode' => 'week', 'date' => $date])); ?>" <?php if($mode==='week'): ?> style="font-weight:bold" <?php endif; ?>>тиждень</a>
    <a href="<?php echo e(route('schedule.index', ['mode' => 'month', 'date' => $date])); ?>" <?php if($mode==='month'): ?> style="font-weight:bold" <?php endif; ?>>місяць</a>
    <span><?php echo e(\Carbon\Carbon::parse($date)->translatedFormat('F Y')); ?></span>
</div>

<?php if($mode === 'day'): ?>
    <h2><?php echo e(\Carbon\Carbon::parse($date)->translatedFormat('l, d F')); ?></h2>
    <?php $__empty_1 = true; $__currentLoopData = $lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div>
        <strong><?php echo e($lesson->start_time); ?> - <?php echo e($lesson->end_time); ?></strong>
        <?php echo e($lesson->course->title); ?>

        <?php echo e($lesson->title ? "({$lesson->title})" : ''); ?>

        [<?php echo e($lesson->mode); ?>]
        <?php if($lesson->location): ?> | <?php echo e($lesson->location->name); ?> <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p>Немає занять на цей день.</p>
    <?php endif; ?>
<?php elseif($mode === 'week'): ?>
    <?php $grouped = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d')); ?>
    <?php for($d = \Carbon\Carbon::parse($date)->startOfWeek(); $d <= \Carbon\Carbon::parse($date)->endOfWeek(); $d->addDay()): ?>
        <h3><?php echo e($d->translatedFormat('D d.m')); ?></h3>
        <?php $__currentLoopData = $grouped->get($d->format('Y-m-d'), collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <?php echo e($lesson->start_time); ?> - <?php echo e($lesson->end_time); ?>

            | <?php echo e($lesson->course->title); ?>

            <?php if($lesson->mode === 'offline' && $lesson->location): ?> | <?php echo e($lesson->location->name); ?> <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endfor; ?>
<?php elseif($mode === 'month'): ?>
    <?php
        $monthStart = \Carbon\Carbon::parse($date)->startOfMonth();
        $monthEnd = \Carbon\Carbon::parse($date)->endOfMonth();
        $grouped = $lessons->groupBy(fn($l) => $l->date->format('Y-m-d'));
    ?>
    <table>
        <thead><tr><th>ПН</th><th>ВТ</th><th>СР</th><th>ЧТ</th><th>ПТ</th><th>СБ</th><th>НД</th></tr></thead>
        <tbody>
        <?php $current = $monthStart->copy()->startOfWeek(); ?>
        <?php while($current <= $monthEnd->copy()->endOfWeek()): ?>
        <tr>
            <?php for($i = 0; $i < 7; $i++): ?>
                <td>
                    <?php echo e($current->day); ?>

                    <?php $__currentLoopData = $grouped->get($current->format('Y-m-d'), collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div><?php echo e($lesson->start_time); ?> <?php echo e(Str::limit($lesson->course->title, 10)); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </td>
                <?php $current->addDay(); ?>
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
    <input type="date" name="date" required>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/schedule/index.blade.php ENDPATH**/ ?>