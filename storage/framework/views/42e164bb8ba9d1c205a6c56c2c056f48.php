<?php $__env->startSection('title', 'Статистика занять'); ?>

<?php $__env->startSection('content'); ?>
<h1>Статистика занять</h1>


<form method="GET" action="<?php echo e(route('superadmin.lesson.stats')); ?>" style="display:flex; gap:8px; margin-bottom:20px;">
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
<div style="border:2px solid #e74c3c; padding:12px; margin-bottom:20px; border-radius:6px;">
    <strong style="color:#e74c3c;">⚠️ Без звіту: <?php echo e($unreported->count()); ?> занять</strong>
    <ul style="margin:8px 0 0;">
        <?php $__currentLoopData = $unreported; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><?php echo e($l->date->format('d.m.Y')); ?> · <?php echo e($l->teacher->full_name ?? '—'); ?> · <?php echo e($l->course->title); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<?php $__empty_1 = true; $__currentLoopData = $byTeacher; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div style="border:1px solid #ddd; padding:15px; margin-bottom:20px; border-radius:6px;">
    <h2 style="margin-top:0;"><?php echo e($data['teacher']->full_name ?? '—'); ?></h2>

    <table style="border-collapse:collapse; margin-bottom:10px;">
        <tr>
            <td style="padding:4px 12px 4px 0;"><strong>Всього занять:</strong></td>
            <td><?php echo e($data['total']); ?></td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Повні:</td>
            <td><?php echo e($data['full']); ?></td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Часткові (інд.):</td>
            <td><?php echo e($data['partial']); ?></td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Скасовані:</td>
            <td><?php echo e($data['cancelled']); ?></td>
        </tr>
        <tr>
            <td style="padding:4px 12px 4px 0;">Перенесені (груп.):</td>
            <td><?php echo e($data['rescheduled']); ?></td>
        </tr>
        <?php if($data['individual_minutes_planned'] > 0): ?>
        <tr>
            <td style="padding:4px 12px 4px 0;"><strong>Інд. годин (план / факт):</strong></td>
            <td><strong><?php echo e(round($data['individual_minutes_planned'] / 60, 1)); ?> / <?php echo e(round($data['individual_minutes_actual'] / 60, 1)); ?> год</strong></td>
        </tr>
        <?php endif; ?>
    </table>

    <details>
        <summary style="cursor:pointer; color:#4a90d9;">Деталі занять</summary>
        <table style="width:100%; border-collapse:collapse; margin-top:8px; font-size:0.9em;">
            <thead>
                <tr style="background:#f5f5f5;">
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Дата</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Курс</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Тип</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Статус</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Год план</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Год факт</th>
                    <th style="padding:5px 8px; text-align:left; border:1px solid #ddd;">Примітка</th>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $data['lessons']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td style="padding:5px 8px; border:1px solid #ddd;"><?php echo e($lesson->date->format('d.m')); ?></td>
                <td style="padding:5px 8px; border:1px solid #ddd;"><?php echo e($lesson->course->title); ?></td>
                <td style="padding:5px 8px; border:1px solid #ddd;"><?php echo e($lesson->course->type === 'individual' ? 'Інд.' : 'Груп.'); ?></td>
                <td style="padding:5px 8px; border:1px solid #ddd;">
                    <?php switch($lesson->completion_status):
                        case ('full'): ?> ✅ Повне <?php break; ?>
                        <?php case ('partial'): ?> ⚡ Часткове <?php break; ?>
                        <?php case ('cancelled'): ?> ❌ Скасовано <?php break; ?>
                        <?php case ('rescheduled'): ?> 🔄 Перенесено <?php break; ?>
                    <?php endswitch; ?>
                </td>
                <td style="padding:5px 8px; border:1px solid #ddd;"><?php echo e(round($lesson->plannedMinutes() / 60, 1)); ?></td>
                <td style="padding:5px 8px; border:1px solid #ddd;">
                    <?php $actMin = $lesson->actual_minutes ?? ($lesson->completion_status === 'full' ? $lesson->plannedMinutes() : null); ?>
                    <?php echo e($actMin !== null ? round($actMin / 60, 1) : '—'); ?>

                </td>
                <td style="padding:5px 8px; border:1px solid #ddd;"><?php echo e($lesson->completion_note ?? '—'); ?></td>
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