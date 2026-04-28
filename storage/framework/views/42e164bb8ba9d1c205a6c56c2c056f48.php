<?php $__env->startSection('title', 'Статистика занять'); ?>

<?php $__env->startSection('content'); ?>
<h1>Статистика занять</h1>

<form method="GET" action="<?php echo e(route('superadmin.lesson.stats')); ?>" class="flex-row mb-3">
    <select name="month">
        <?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($m); ?>" <?php if($m == $month): echo 'selected'; endif; ?>>
                <?php echo e(\Carbon\Carbon::create()->month($m)->translatedFormat('F')); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="year">
        <?php $__currentLoopData = range(now()->year - 1, now()->year + 1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($y); ?>" <?php if($y == $year): echo 'selected'; endif; ?>><?php echo e($y); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button type="submit">Показати</button>
</form>

<?php if($unreported->count()): ?>
<div class="unreported-section">
    <strong>⚠️ Без звіту: <?php echo e($unreported->count()); ?> занять</strong>
    <ul>
        <?php $__currentLoopData = $unreported; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($l->date->format('d.m.Y')); ?> · <?php echo e($l->teacher->full_name ?? '—'); ?> · <?php echo e($l->course->title); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<?php $__empty_1 = true; $__currentLoopData = $byTeacher; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="card-panel mb-2">
    <h2><?php echo e($data['teacher']->full_name ?? '—'); ?></h2>

    <table class="stats-table mb-1">
        <tr>
            <td><strong>Всього занять:</strong></td>
            <td><?php echo e($data['total']); ?></td>
        </tr>
        <tr>
            <td>Повні:</td>
            <td><?php echo e($data['full']); ?></td>
        </tr>
        <tr>
            <td>Часткові (інд.):</td>
            <td><?php echo e($data['partial']); ?></td>
        </tr>
        <tr>
            <td>Скасовані:</td>
            <td><?php echo e($data['cancelled']); ?></td>
        </tr>
        <tr>
            <td>Перенесені (груп.):</td>
            <td><?php echo e($data['rescheduled']); ?></td>
        </tr>
        <?php if($data['individual_minutes_planned'] > 0): ?>
        <tr>
            <td><strong>Інд. годин (план / факт):</strong></td>
            <td><strong><?php echo e(round($data['individual_minutes_planned'] / 60, 1)); ?> / <?php echo e(round($data['individual_minutes_actual'] / 60, 1)); ?> год</strong></td>
        </tr>
        <?php endif; ?>
    </table>

    <details>
        <summary class="text-blue">Деталі занять</summary>
        <table class="data-table data-table--bordered text-sm mt-1">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Курс</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Год план</th>
                    <th>Год факт</th>
                    <th>Примітка</th>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $data['lessons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($lesson->date->format('d.m')); ?></td>
                <td><?php echo e($lesson->course->title); ?></td>
                <td><?php echo e($lesson->course->type === 'individual' ? 'Інд.' : 'Груп.'); ?></td>
                <td>
                    <?php switch($lesson->completion_status):
                        case ('full'): ?> ✅ Повне <?php break; ?>
                        <?php case ('partial'): ?> ⚡ Часткове <?php break; ?>
                        <?php case ('cancelled'): ?> ❌ Скасовано <?php break; ?>
                        <?php case ('rescheduled'): ?> 🔄 Перенесено <?php break; ?>
                    <?php endswitch; ?>
                </td>
                <td><?php echo e(round($lesson->plannedMinutes() / 60, 1)); ?></td>
                <td>
                    <?php $actMin = $lesson->actual_minutes ?? ($lesson->completion_status === 'full' ? $lesson->plannedMinutes() : null); ?>
                    <?php echo e($actMin !== null ? round($actMin / 60, 1) : '—'); ?>

                </td>
                <td><?php echo e($lesson->completion_note ?? '—'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </details>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<p>Немає даних за обраний місяць.</p>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/superadmin/lesson-stats.blade.php ENDPATH**/ ?>