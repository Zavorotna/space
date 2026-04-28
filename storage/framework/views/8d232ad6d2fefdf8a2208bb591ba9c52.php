<?php $__env->startSection('title', 'Мої сертифікати'); ?>

<?php $__env->startSection('content'); ?>
<h1>Мої сертифікати</h1>

<?php if($certificates->isEmpty()): ?>
    <p>У вас ще немає сертифікатів. Завершіть курс, щоб отримати сертифікат.</p>
<?php else: ?>
    <?php $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card-panel">
        <h3><?php echo e($cert->course->title ?? '—'); ?></h3>
        <p>Тип:
            <?php switch($cert->type):
                case ('bw'): ?> Чорно-білий (Прослухав) <?php break; ?>
                <?php case ('color'): ?> Кольоровий (Старався) — знижка 10% <?php break; ?>
                <?php case ('guaranteed'): ?> З гарантією (Відмінний результат) — знижка 20% <?php break; ?>
            <?php endswitch; ?>
        </p>
        <p>Успішність: <?php echo e($cert->success_rate); ?>%</p>
        <p>Номер: <?php echo e($cert->certificate_number); ?></p>
        <?php if($cert->discount_next_course > 0): ?>
            <p>Знижка на наступний курс: <?php echo e($cert->discount_next_course); ?>%
                <?php echo e($cert->discount_used ? '(використана)' : ''); ?>

            </p>
        <?php endif; ?>
        <a href="<?php echo e(route('certificates.show', $cert)); ?>">Переглянути</a>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/student/certificates.blade.php ENDPATH**/ ?>