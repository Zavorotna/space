<?php $__env->startSection('title', 'Курси'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header">
    <h1>Курси</h1>
    <div>
        <a href="<?php echo e(route('teacher.courses.create')); ?>">+ Новий курс</a>
    </div>
</div>

<?php if(session('success')): ?>
    <p class="text-success mb-1"><?php echo e(session('success')); ?></p>
<?php endif; ?>

<h2>Шаблони</h2>
<?php if($templates->isEmpty()): ?>
    <p>Шаблонів ще немає. Створіть шаблон, щоб швидко запускати нові курси.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Назва</th>
                <th>Тип</th>
                <th>Ціна</th>
                <?php if(auth()->user()->isAdmin()): ?> <th>Викладач</th> <?php endif; ?>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($course->title); ?></td>
                <td><?php echo e($course->type === 'group' ? 'Груповий' : 'Індивідуальний'); ?></td>
                <td><?php echo e($course->price); ?> грн</td>
                <?php if(auth()->user()->isAdmin()): ?>
                    <td><?php echo e($course->teacher?->full_name ?? '—'); ?></td>
                <?php endif; ?>
                <td>
                    <a href="<?php echo e(route('teacher.courses.edit', $course)); ?>">Редагувати</a>
                    <form method="POST" action="<?php echo e(route('teacher.courses.duplicate', $course)); ?>" class="form-inline"
                          onsubmit="this.querySelector('button').disabled = true">
                        <?php echo csrf_field(); ?>
                        <button type="submit">Копіювати як курс</button>
                    </form>
                    <?php if(auth()->user()->isSuperAdmin()): ?>
                    <form method="POST" action="<?php echo e(route('teacher.courses.destroy', $course)); ?>" class="form-inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" onclick="return confirm('Видалити курс «<?php echo e($course->title); ?>»?')">Видалити</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php endif; ?>

<h2>Мої курси</h2>
<?php if($courses->isEmpty()): ?>
    <p>Курсів ще немає.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Назва</th>
                <th>Тип</th>
                <th>Статус</th>
                <th>Початок</th>
                <th>Кінець</th>
                <?php if(auth()->user()->isAdmin()): ?> <th>Викладач</th> <?php endif; ?>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($course->title); ?></td>
                <td><?php echo e($course->type === 'group' ? 'Груповий' : 'Індивідуальний'); ?></td>
                <td>
                    <?php switch($course->status):
                        case ('waiting'): ?> Очікує <?php break; ?>
                        <?php case ('enrolling'): ?> Набір <?php break; ?>
                        <?php case ('active'): ?> Активний <?php break; ?>
                        <?php case ('completed'): ?> Завершений <?php break; ?>
                    <?php endswitch; ?>
                    <?php if($course->is_published): ?> ✅ <?php else: ?> ❌ <?php endif; ?>
                </td>
                <td><?php echo e($course->start_date?->format('d.m.Y') ?? '—'); ?></td>
                <td><?php echo e($course->end_date?->format('d.m.Y') ?? '—'); ?></td>
                <?php if(auth()->user()->isAdmin()): ?>
                    <td><?php echo e($course->teacher?->full_name ?? '—'); ?></td>
                <?php endif; ?>
                <td>
                    <a href="<?php echo e(route('teacher.courses.edit', $course)); ?>">Редагувати</a>
                    <form method="POST" action="<?php echo e(route('teacher.courses.duplicate', $course)); ?>" class="form-inline"
                          onsubmit="this.querySelector('button').disabled = true">
                        <?php echo csrf_field(); ?>
                        <button type="submit">Копіювати</button>
                    </form>
                    <?php if(auth()->user()->isSuperAdmin()): ?>
                    <form method="POST" action="<?php echo e(route('teacher.courses.destroy', $course)); ?>" class="form-inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" onclick="return confirm('Видалити курс «<?php echo e($course->title); ?>»?')">Видалити</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/courses.blade.php ENDPATH**/ ?>