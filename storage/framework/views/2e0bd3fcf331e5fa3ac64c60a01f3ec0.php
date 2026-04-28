<?php $__env->startSection('title', 'Батьківський кабінет'); ?>

<?php $__env->startSection('content'); ?>
<h1>Батьківський кабінет</h1>

<?php if(empty($childrenData)): ?>
    <p>У вас ще немає прив'язаних дітей. Зверніться до адміністрації для зв'язування акаунтів.</p>
<?php else: ?>
    <?php $__currentLoopData = $childrenData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $child = $data['child']; ?>
    <div class="parent-child-block">
        <h2><?php echo e($child->last_name); ?> <?php echo e($child->first_name); ?></h2>

        
        <h3>Активні курси</h3>
        <?php if($data['courses']->count()): ?>
            <?php $__currentLoopData = $data['courses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card">
                    <p><strong><?php echo e($course->title); ?></strong></p>
                    <p>Викладач: <?php echo e($course->teacher->last_name ?? ''); ?> <?php echo e($course->teacher->first_name ?? ''); ?></p>
                    <p>Успішність: <?php echo e($course->pivot->success_rate ?? 0); ?>%</p>
                    <p>Оплата: <?php echo e($course->pivot->is_paid ? '✅ Оплачено' : '❌ Не оплачено'); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <p>Немає активних курсів.</p>
        <?php endif; ?>

        
        <h3>Відвідуваність (останні 10)</h3>
        <?php if($data['recentAttendances']->count()): ?>
            <table>
                <thead><tr><th>Дата</th><th>Курс</th><th>Статус</th></tr></thead>
                <tbody>
                <?php $__currentLoopData = $data['recentAttendances']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($att->lesson?->date?->format('d.m.Y') ?? '—'); ?> <?php echo e($att->lesson?->start_time ?? ''); ?></td>
                        <td><?php echo e($att->lesson?->course?->title ?? '—'); ?></td>
                        <td><?php echo e($att->status === 'present' ? '✅ Присутній' : '❌ Відсутній'); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Немає даних про відвідуваність.</p>
        <?php endif; ?>

        
        <h3>Замітки від викладачів (останні 10)</h3>
        <?php if($data['notes']->count()): ?>
            <?php $__currentLoopData = $data['notes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="card">
                    <p><strong><?php echo e($note->author->last_name ?? ''); ?> <?php echo e($note->author->first_name ?? ''); ?></strong>
                        — <?php echo e($note->created_at->format('d.m.Y H:i')); ?></p>
                    <p><?php echo e($note->content); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <p>Немає заміток.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/parent/dashboard.blade.php ENDPATH**/ ?>