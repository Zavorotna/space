<?php $__env->startSection('title', 'Резюме: ' . $resume->user->last_name . ' ' . $resume->user->first_name); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('resumes.index')); ?>">&larr; Всі резюме</a>

<div>
    <?php if($resume->user->getFirstMediaUrl('avatar')): ?>
        <img src="<?php echo e($resume->user->getFirstMediaUrl('avatar')); ?>" alt="Аватар" style="width:100px; height:100px; border-radius:50%;">
    <?php endif; ?>

    
    <?php if($resume->user->isVip() && $resume->user->getMedia('extra_avatars')->count()): ?>
        <div id="avatar-slider">
            <?php $__currentLoopData = $resume->user->getMedia('extra_avatars'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $avatar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <img src="<?php echo e($avatar->getUrl()); ?>" alt="Аватар <?php echo e($i+1); ?>"
                     style="width:80px; height:80px; border-radius:50%; display:<?php echo e($i === 0 ? 'inline-block' : 'none'); ?>;"
                     class="extra-avatar">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($resume->user->getMedia('extra_avatars')->count() > 1): ?>
                <button onclick="slideAvatar()">→</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h1><?php echo e($resume->user->last_name); ?> <?php echo e($resume->user->first_name); ?>

        <?php if($resume->user->isVip()): ?> ⭐ VIP <?php endif; ?>
    </h1>
</div>

<?php if($resume->about): ?>
    <h2>Про себе</h2>
    <p><?php echo nl2br(e($resume->about)); ?></p>
<?php endif; ?>


<h2>Завершені курси</h2>
<?php $visibleCerts = $resume->user->certificates->filter(fn($c) => !in_array($c->course_id, $resume->hidden_courses ?? [])); ?>
<?php if($visibleCerts->count()): ?>
    <table>
        <thead><tr><th>Курс</th><th>Успішність</th><th>Сертифікат</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $visibleCerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($cert->course->title ?? '—'); ?></td>
                <td><?php echo e($cert->success_rate); ?>%</td>
                <td>
                    <?php switch($cert->type):
                        case ('bw'): ?> Чорно-білий <?php break; ?>
                        <?php case ('color'): ?> Кольоровий <?php break; ?>
                        <?php case ('guaranteed'): ?> З гарантією <?php break; ?>
                    <?php endswitch; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Немає завершених курсів.</p>
<?php endif; ?>

<?php if($resume->work_experience): ?>
    <h2>Досвід роботи</h2>
    <p><?php echo nl2br(e($resume->work_experience)); ?></p>
<?php endif; ?>

<?php if($resume->project_links && count($resume->project_links)): ?>
    <h2>Проєкти</h2>
    <ul>
    <?php $__currentLoopData = $resume->project_links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><a href="<?php echo e($link); ?>" target="_blank"><?php echo e($link); ?></a></li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
<?php endif; ?>

<h2>Контакти</h2>
<?php if($resume->contact_email): ?> <p>Email: <?php echo e($resume->contact_email); ?></p> <?php endif; ?>
<?php if($resume->contact_phone): ?> <p>Телефон: <?php echo e($resume->contact_phone); ?></p> <?php endif; ?>

<script>
let currentAvatar = 0;
function slideAvatar() {
    const avatars = document.querySelectorAll('.extra-avatar');
    if (!avatars.length) return;
    avatars[currentAvatar].style.display = 'none';
    currentAvatar = (currentAvatar + 1) % avatars.length;
    avatars[currentAvatar].style.display = 'inline-block';
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/resume/show.blade.php ENDPATH**/ ?>