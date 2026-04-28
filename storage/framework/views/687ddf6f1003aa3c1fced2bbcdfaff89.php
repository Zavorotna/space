<?php $__env->startSection('title', 'Сертифікат: ' . $certificate->course->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('certificates.index')); ?>">&larr; Всі сертифікати</a>

<h1>Сертифікат</h1>

<div>
    <p><strong>Курс:</strong> <?php echo e($certificate->course->title); ?></p>
    <p><strong>Студент:</strong> <?php echo e($certificate->user->last_name); ?> <?php echo e($certificate->user->first_name); ?></p>
    <p><strong>Тип:</strong>
        <?php switch($certificate->type):
            case ('bw'): ?> Чорно-білий (Прослухав) <?php break; ?>
            <?php case ('color'): ?> Кольоровий (Старався) <?php break; ?>
            <?php case ('guaranteed'): ?> З гарантією (Відмінний результат) <?php break; ?>
        <?php endswitch; ?>
    </p>
    <p><strong>Успішність:</strong> <?php echo e($certificate->success_rate); ?>%</p>
    <p><strong>Номер:</strong> <?php echo e($certificate->certificate_number); ?></p>
    <p><strong>Дата видачі:</strong> <?php echo e($certificate->created_at->format('d.m.Y')); ?></p>

    <?php if($certificate->discount_next_course > 0): ?>
        <p><strong>Знижка на наступний курс:</strong> <?php echo e($certificate->discount_next_course); ?>%</p>
    <?php endif; ?>
</div>


<?php if($certificate->getFirstMedia('certificate_image')): ?>
    <div>
        <h3>Зображення сертифіката</h3>
        <img src="<?php echo e($certificate->getFirstMediaUrl('certificate_image')); ?>" alt="Сертифікат" style="max-width:100%;">
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/student/certificate-show.blade.php ENDPATH**/ ?>