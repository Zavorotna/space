<?php $__env->startSection('title', 'Дашборд викладача'); ?>
<?php $__env->startSection('content'); ?>
<h1>Дашборд</h1>


<?php echo $__env->make('partials._calendar', [
    'schedDate'      => $schedDate,
    'schedMode'      => $schedMode,
    'schedLessons'   => $schedLessons,
    'schedEvents'    => $schedEvents,
    'schedLocations' => $schedLocations,
    'schedCourses'   => $schedCourses,
    'canEdit'        => true,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php if($lessonsNeedingReport->count()): ?>
<div style="border:2px solid #e67e22;padding:15px;margin:15px 0;border-radius:8px;">
    <h2 style="color:#e67e22;margin-top:0;">Потрібен звіт (<?php echo e($lessonsNeedingReport->count()); ?>)</h2>
    <?php $__currentLoopData = $lessonsNeedingReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $isIndividual = $lesson->course->type === 'individual'; ?>
    <div style="border:1px solid #ddd;padding:10px;margin:8px 0;border-radius:4px;">
        <strong><?php echo e($lesson->date->format('d.m.Y')); ?></strong>
        · <?php echo e($lesson->course->title); ?>

        <?php echo e($lesson->title ? "· {$lesson->title}" : ''); ?>

        · <?php echo e($lesson->start_time); ?>–<?php echo e($lesson->end_time); ?>

        (<?php echo e($lesson->plannedMinutes()); ?> хв)
        <span style="color:#888;font-size:.85em;"><?php echo e($isIndividual ? 'Індивідуальне' : 'Групове'); ?></span>

        <form method="POST" action="<?php echo e(route('teacher.schedule.complete', $lesson)); ?>" style="margin-top:8px;">
            <?php echo csrf_field(); ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;">
                <div>
                    <label>Статус</label><br>
                    <?php if($isIndividual): ?>
                        <select name="completion_status" required id="status-<?php echo e($lesson->id); ?>"
                                onchange="toggleFields(<?php echo e($lesson->id); ?>, this.value, true)">
                            <option value="full" selected>Повне заняття</option>
                            <option value="partial">Часткове</option>
                            <option value="cancelled">Скасовано</option>
                        </select>
                    <?php else: ?>
                        <select name="completion_status" required id="status-<?php echo e($lesson->id); ?>"
                                onchange="toggleFields(<?php echo e($lesson->id); ?>, this.value, false)">
                            <option value="full" selected>Повне заняття</option>
                            <option value="cancelled">Скасовано</option>
                            <option value="rescheduled">Перенесено</option>
                        </select>
                    <?php endif; ?>
                </div>
                <?php if($isIndividual): ?>
                <div id="minutes-<?php echo e($lesson->id); ?>" style="display:none;">
                    <label>Фактично годин</label><br>
                    <input type="number" name="actual_hours" min="0.5" max="10" step="0.5"
                           placeholder="<?php echo e(round($lesson->plannedMinutes() / 60, 1)); ?>" style="width:70px;">
                </div>
                <?php endif; ?>
                <div>
                    <label>Примітка</label><br>
                    <input type="text" name="completion_note" placeholder="необов'язково" style="width:200px;">
                </div>
            </div>
            <div id="makeup-<?php echo e($lesson->id); ?>" style="display:none;margin-top:8px;padding:8px;background:#fff8e1;border-radius:4px;">
                <label><input type="checkbox" name="schedule_makeup" value="1"
                              id="makeup-cb-<?php echo e($lesson->id); ?>"
                              onchange="toggleMakeupDate(<?php echo e($lesson->id); ?>)">
                    Запланувати відпрацювання</label>
                <div id="makeup-date-<?php echo e($lesson->id); ?>" style="display:none;margin-top:6px;">
                    <input type="date" name="makeup_date" style="margin-right:4px;">
                    <input type="time" name="makeup_start" style="margin-right:4px;">
                    <input type="time" name="makeup_end">
                </div>
            </div>
            <button type="submit" style="margin-top:8px;">Зберегти звіт</button>
        </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<script>
function toggleFields(id, status, isIndividual) {
    const minutesEl = document.getElementById('minutes-' + id);
    const makeupEl  = document.getElementById('makeup-' + id);
    if (minutesEl) minutesEl.style.display = (status === 'partial') ? 'block' : 'none';
    if (makeupEl)  makeupEl.style.display  = (status === 'cancelled' || status === 'rescheduled') ? 'block' : 'none';
}
function toggleMakeupDate(id) {
    const cb = document.getElementById('makeup-cb-' + id);
    document.getElementById('makeup-date-' + id).style.display = cb.checked ? 'block' : 'none';
}
</script>
<?php endif; ?>

<h2>Курси</h2>
<?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div style="margin-bottom:8px;">
    <strong><?php echo e($course->title); ?></strong>
    <?php
        $progress = 0;
        if ($course->start_date && $course->end_date) {
            $total   = $course->start_date->diffInDays($course->end_date);
            $elapsed = $course->start_date->diffInDays(now());
            $progress = $total > 0 ? min(100, round($elapsed / $total * 100)) : 0;
        }
    ?>
    <?php echo e($progress); ?>%
    <progress value="<?php echo e($progress); ?>" max="100"></progress>
    <span style="font-size:.85em;color:#888;"><?php echo e($course->start_date?->format('d.m')); ?> — <?php echo e($course->end_date?->format('d.m')); ?></span>
    <?php if($course->applications()->where('status','pending')->count() > 0): ?>
    <a href="<?php echo e(route('teacher.courses.applications', $course)); ?>" style="font-size:.85em;">
        <?php echo e($course->applications()->where('status','pending')->count()); ?> заявок
    </a>
    <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($pendingHomework > 0): ?>
<p>Домашок на перевірку: <strong><?php echo e($pendingHomework); ?></strong></p>
<?php endif; ?>

<h2>Замітки</h2>
<?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div><?php echo e($note->content); ?></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<form method="POST" action="<?php echo e(route('notes.store')); ?>">
    <?php echo csrf_field(); ?>
    <textarea name="content" placeholder="Нова замітка..." required></textarea>
    <button type="submit">Зберегти</button>
</form>

<h2>Гаманець</h2>
<p>Баланс: <strong><?php echo e($wallet->balance); ?></strong></p>
<a href="<?php echo e(route('wallet.transfer')); ?>">переказати</a>
<a href="<?php echo e(route('wallet.topup')); ?>">поповнити</a>
<a href="<?php echo e(route('wallet.withdraw')); ?>">вивести</a>

<h3>Транзакції</h3>
<table>
    <thead><tr><th>Дата</th><th>Опис</th><th>Сума</th></tr></thead>
    <tbody>
    <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td><?php echo e($tx->created_at->format('d.m.y')); ?></td>
        <td><?php echo e($tx->description); ?></td>
        <td><?php echo e($tx->amount > 0 ? '+' : ''); ?><?php echo e($tx->amount); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/dashboard.blade.php ENDPATH**/ ?>