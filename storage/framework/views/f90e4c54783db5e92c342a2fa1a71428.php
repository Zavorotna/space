<?php $__env->startSection('title', 'Адмін панель'); ?>
<?php $__env->startSection('content'); ?>
<h1>Адмін панель</h1>

<?php if($pendingApplications > 0): ?>
<p style="margin-bottom:12px;">
    Заявок на розгляді: <strong><?php echo e($pendingApplications); ?></strong>
</p>
<?php endif; ?>

<?php if(auth()->user()->isSuperAdmin() && $pendingWithdrawalsList->count() > 0): ?>
<div style="border:2px solid #e67e22;padding:14px;margin-bottom:20px;border-radius:8px;">
    <h2 style="color:#e67e22;margin:0 0 10px;">Запити на виведення (<?php echo e($pendingWithdrawalsList->count()); ?>)</h2>
    <?php $__currentLoopData = $pendingWithdrawalsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="border:1px solid #eee;padding:10px;margin:6px 0;border-radius:5px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start;">
        <div>
            <strong><?php echo e($req->user->last_name); ?> <?php echo e($req->user->first_name); ?></strong><br>
            <span style="font-size:.85rem;color:#888;"><?php echo e($req->amount); ?> монет · <?php echo e($req->created_at->format('d.m.Y H:i')); ?></span>
        </div>
        <form method="POST" action="<?php echo e(route('superadmin.withdrawals.approve', $req)); ?>" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
            <?php echo csrf_field(); ?>
            <input type="text" name="pickup_note" placeholder="Куди підійти" required style="width:180px;">
            <button type="submit" style="background:#27ae60;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">Видати готівку</button>
        </form>
        <form method="POST" action="<?php echo e(route('superadmin.withdrawals.reject', $req)); ?>"
              onsubmit="return confirm('Відхилити? Монети повернуться.')">
            <?php echo csrf_field(); ?>
            <button type="submit" style="background:#e74c3c;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">Відхилити</button>
        </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($lessonsNeedingReport->count()): ?>
<div style="border:2px solid #e67e22;padding:15px;margin-bottom:20px;border-radius:8px;">
    <h2 style="color:#e67e22;margin-top:0;">Потрібен звіт (<?php echo e($lessonsNeedingReport->count()); ?>)</h2>
    <?php $__currentLoopData = $lessonsNeedingReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $isIndividual = $lesson->course->type === 'individual'; ?>
    <div style="border:1px solid #ddd;padding:10px;margin:8px 0;border-radius:4px;">
        <strong><?php echo e($lesson->date->format('d.m.Y')); ?></strong>
        · <?php echo e($lesson->course->title); ?>

        <?php echo e($lesson->title ? "· {$lesson->title}" : ''); ?>

        · <?php echo e(substr($lesson->start_time,0,5)); ?>–<?php echo e(substr($lesson->end_time,0,5)); ?>

        (<?php echo e($lesson->plannedMinutes()); ?> хв)
        <span style="color:#888;font-size:.85em;"><?php echo e($isIndividual ? 'Індивідуальне' : 'Групове'); ?></span>

        <form method="POST" action="<?php echo e(route('teacher.schedule.complete', $lesson)); ?>" style="margin-top:8px;">
            <?php echo csrf_field(); ?>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;">
                <div>
                    <label>Статус</label><br>
                    <?php if($isIndividual): ?>
                    <select name="completion_status" required id="adm-status-<?php echo e($lesson->id); ?>"
                            onchange="admToggle(<?php echo e($lesson->id); ?>, this.value, true)">
                        <option value="full" selected>Повне заняття</option>
                        <option value="partial">Часткове</option>
                        <option value="cancelled">Скасовано</option>
                    </select>
                    <?php else: ?>
                    <select name="completion_status" required id="adm-status-<?php echo e($lesson->id); ?>"
                            onchange="admToggle(<?php echo e($lesson->id); ?>, this.value, false)">
                        <option value="full" selected>Повне заняття</option>
                        <option value="cancelled">Скасовано</option>
                        <option value="rescheduled">Перенесено</option>
                    </select>
                    <?php endif; ?>
                </div>
                <?php if($isIndividual): ?>
                <div id="adm-minutes-<?php echo e($lesson->id); ?>" style="display:none;">
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
            <div id="adm-makeup-<?php echo e($lesson->id); ?>" style="display:none;margin-top:8px;padding:8px;background:#fff8e1;border-radius:4px;">
                <label>
                    <input type="checkbox" name="schedule_makeup" value="1"
                           id="adm-makeup-cb-<?php echo e($lesson->id); ?>"
                           onchange="admMakeupDate(<?php echo e($lesson->id); ?>)">
                    Запланувати відпрацювання
                </label>
                <div id="adm-makeup-date-<?php echo e($lesson->id); ?>" style="display:none;margin-top:6px;">
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
<?php $__env->startPush('scripts'); ?>
<script>
function admToggle(id, status, isInd) {
    const m = document.getElementById('adm-minutes-' + id);
    const mk = document.getElementById('adm-makeup-' + id);
    if (m)  m.style.display  = (status === 'partial') ? 'block' : 'none';
    if (mk) mk.style.display = (status === 'cancelled' || status === 'rescheduled') ? 'block' : 'none';
}
function admMakeupDate(id) {
    const cb = document.getElementById('adm-makeup-cb-' + id);
    document.getElementById('adm-makeup-date-' + id).style.display = cb.checked ? 'block' : 'none';
}
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>


<?php echo $__env->make('partials._calendar', [
    'schedDate'      => $schedDate,
    'schedMode'      => $schedMode,
    'schedLessons'   => $schedLessons,
    'schedEvents'    => $schedEvents,
    'schedLocations' => $schedLocations,
    'schedCourses'   => $schedCourses,
    'canEdit'        => true,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(auth()->user()->isSuperAdmin()): ?>
<h2 style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    Останні транзакції
    <a href="<?php echo e(route('superadmin.transactions')); ?>" style="font-size:.82rem;font-weight:normal;">Всі →</a>
</h2>
<table style="width:100%;border-collapse:collapse;font-size:.88rem;">
    <thead>
        <tr style="background:#f7f8fa;">
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Дата</th>
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Користувач</th>
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Тип</th>
            <th style="padding:6px 8px;text-align:left;border-bottom:1px solid #eee;">Опис</th>
            <th style="padding:6px 8px;text-align:right;border-bottom:1px solid #eee;">Сума</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;color:#888;"><?php echo e($tx->created_at->format('d.m.y H:i')); ?></td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;"><?php echo e($tx->user->full_name); ?></td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;color:#888;"><?php echo e($tx->type); ?></td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;"><?php echo e($tx->description); ?></td>
        <td style="padding:5px 8px;border-bottom:1px solid #f5f5f5;text-align:right;font-weight:600;color:<?php echo e($tx->amount >= 0 ? '#27ae60' : '#e74c3c'); ?>;"><?php echo e($tx->amount > 0 ? '+' : ''); ?><?php echo e($tx->amount); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>