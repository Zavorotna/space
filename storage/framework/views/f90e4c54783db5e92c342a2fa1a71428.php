<?php $__env->startSection('title', 'Адмін панель'); ?>
<?php $__env->startSection('content'); ?>
<h1>Адмін панель</h1>

<?php echo $__env->make('partials._admin_banners', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if($pendingApplications > 0): ?>
<p class="mb-1">
    Заявок на розгляді: <strong><?php echo e($pendingApplications); ?></strong>
</p>
<?php endif; ?>

<?php if(auth()->user()->isSuperAdmin() && $pendingWithdrawalsList->count() > 0): ?>
<div class="withdrawal-section">
    <h2>Запити на виведення (<?php echo e($pendingWithdrawalsList->count()); ?>)</h2>
    <?php $__currentLoopData = $pendingWithdrawalsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="withdrawal-item">
        <div>
            <strong><?php echo e($req->user->last_name); ?> <?php echo e($req->user->first_name); ?></strong><br>
            <span class="text-sm text-muted"><?php echo e($req->amount); ?> монет · <?php echo e($req->created_at->format('d.m.Y H:i')); ?></span>
        </div>
        <form method="POST" action="<?php echo e(route('superadmin.withdrawals.approve', $req)); ?>" class="flex-row">
            <?php echo csrf_field(); ?>
            <input type="text" name="pickup_note" placeholder="Куди підійти" required class="input-w-180">
            <button type="submit" class="btn btn-sm btn-success">Видати готівку</button>
        </form>
        <form method="POST" action="<?php echo e(route('superadmin.withdrawals.reject', $req)); ?>"
              onsubmit="return confirm('Відхилити? Монети повернуться.')">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-sm btn-danger">Відхилити</button>
        </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($lessonsNeedingReport->count()): ?>
<div class="report-section">
    <h2>Потрібен звіт (<?php echo e($lessonsNeedingReport->count()); ?>)</h2>
    <?php $__currentLoopData = $lessonsNeedingReport; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $isIndividual = $lesson->course->type === 'individual'; ?>
    <div class="report-item">
        <strong><?php echo e($lesson->date->format('d.m.Y')); ?></strong>
        · <?php echo e($lesson->course->title); ?>

        <?php echo e($lesson->title ? "· {$lesson->title}" : ''); ?>

        · <?php echo e(substr($lesson->start_time,0,5)); ?>–<?php echo e(substr($lesson->end_time,0,5)); ?>

        (<?php echo e($lesson->plannedMinutes()); ?> хв)
        <span class="text-sm text-muted"><?php echo e($isIndividual ? 'Індивідуальне' : 'Групове'); ?></span>

        <form method="POST" action="<?php echo e(route('teacher.schedule.complete', $lesson)); ?>" class="mt-1">
            <?php echo csrf_field(); ?>
            <div class="flex-row flex-start">
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
                           placeholder="<?php echo e(round($lesson->plannedMinutes() / 60, 1)); ?>" class="input-sm">
                </div>
                <?php endif; ?>
                <div>
                    <label>Примітка</label><br>
                    <input type="text" name="completion_note" placeholder="необов'язково" class="input-md">
                </div>
            </div>
            <div id="adm-makeup-<?php echo e($lesson->id); ?>" class="makeup-panel" style="display:none;">
                <label>
                    <input type="checkbox" name="schedule_makeup" value="1"
                           id="adm-makeup-cb-<?php echo e($lesson->id); ?>"
                           onchange="admMakeupDate(<?php echo e($lesson->id); ?>)">
                    Запланувати відпрацювання
                </label>
                <div id="adm-makeup-date-<?php echo e($lesson->id); ?>" class="makeup-date-row" style="display:none;">
                    <input type="date" name="makeup_date">
                    <input type="time" name="makeup_start">
                    <input type="time" name="makeup_end">
                </div>
            </div>
            <button type="submit" class="btn mt-1">Зберегти звіт</button>
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
    document.getElementById('adm-makeup-date-' + id).style.display = cb.checked ? 'flex' : 'none';
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
    'schedBirthdays' => $schedBirthdays,
    'canEdit'        => true,
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php if(auth()->user()->isSuperAdmin()): ?>
<h2 class="section-header">
    Останні транзакції
    <a href="<?php echo e(route('superadmin.transactions')); ?>" class="text-sm">Всі →</a>
</h2>
<table class="data-table">
    <thead>
        <tr>
            <th>Дата</th>
            <th>Користувач</th>
            <th>Тип</th>
            <th>Опис</th>
            <th style="text-align:right;">Сума</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $recentTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
        <td class="text-muted"><?php echo e($tx->created_at->format('d.m.y H:i')); ?></td>
        <td><?php echo e($tx->user->full_name); ?></td>
        <td class="text-muted"><?php echo e($tx->type); ?></td>
        <td><?php echo e($tx->description); ?></td>
        <td style="text-align:right;font-weight:600;color:<?php echo e($tx->amount >= 0 ? '#27ae60' : '#e74c3c'); ?>;"><?php echo e($tx->amount > 0 ? '+' : ''); ?><?php echo e($tx->amount); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>