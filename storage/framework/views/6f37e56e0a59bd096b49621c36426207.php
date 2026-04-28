<?php $__env->startSection('title', 'Бонуси'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('wallet.index')); ?>">&larr; Гаманець</a>

<h1>Бонуси</h1>


<h2>Придбати бонуси</h2>
<form method="POST" action="<?php echo e(route('bonuses.purchase')); ?>">
    <?php echo csrf_field(); ?>
    <div>
        <label>Тип бонусу</label>
        <select name="type" id="bonus-type">
            <option value="test_hint">Підказка на тесті (-15 монет)</option>
            <option value="homework_freeze">Заморозка дедлайну ДЗ (-15 монет/день)</option>
            <option value="graduation_freeze">Заморозка дедлайну випускного (-50 монет/день)</option>
        </select>
    </div>
    <div>
        <label>Курс</label>
        <select name="course_id">
            <?php $__currentLoopData = auth()->user()->activeEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($course->id); ?>"><?php echo e($course->title); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div>
        <label>Кількість</label>
        <input type="number" name="quantity" value="1" min="1" max="20">
    </div>
    <button type="submit">Придбати</button>
</form>

<hr>


<h2>Інвентар</h2>

<?php if($inventory->isEmpty()): ?>
    <p>У вас ще немає бонусів.</p>
<?php else: ?>
    <?php $__currentLoopData = $inventory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $courseId => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $course = $items->first()->course; ?>
        <h3><?php echo e($course?->title ?? 'Курс #' . $courseId); ?></h3>
        <table>
            <thead>
                <tr>
                    <th>Тип</th>
                    <th>Залишок</th>
                    <th>Використано</th>
                    <th>Дія</th>
                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <?php switch($item->type):
                            case ('test_hint'): ?> Підказка на тесті <?php break; ?>
                            <?php case ('homework_freeze'): ?> Заморозка ДЗ <?php break; ?>
                            <?php case ('graduation_freeze'): ?> Заморозка випускного <?php break; ?>
                        <?php endswitch; ?>
                    </td>
                    <td><?php echo e($item->quantity); ?></td>
                    <td><?php echo e($item->used_count); ?></td>
                    <td>
                        <?php if($item->quantity > 0): ?>
                        <form method="POST" action="<?php echo e(route('bonuses.sell', $item)); ?>" class="form-inline"
                              onsubmit="return confirm('Продати за -10%?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit">Продати (-10%)</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<p><em>VIP-знижка на бонуси: 10%</em></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/wallet/bonuses.blade.php ENDPATH**/ ?>