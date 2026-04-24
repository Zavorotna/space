<?php $__env->startSection('title', 'Управління користувачами'); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('dashboard')); ?>">&larr; Дашборд</a>

<h1>Користувачі</h1>


<form method="GET" action="<?php echo e(route('admin.users')); ?>">
    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Пошук за ім'ям, прізвищем, телефоном...">
    <select name="role">
        <option value="">— Всі ролі —</option>
        <?php $__currentLoopData = ['superadmin','admin','teacher','student','parent','registered']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($r); ?>" <?php if(request('role') === $r): echo 'selected'; endif; ?>><?php echo e($r); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button type="submit">Фільтрувати</button>
</form>

<hr>

<table>
    <thead>
        <tr>
            <th>ID</th><th>Ім'я</th><th>Прізвище</th><th>Телефон</th><th>Роль</th><th>VIP</th><th>Серія</th><th>Дії</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($u->id); ?></td>
            <td><?php echo e($u->first_name); ?></td>
            <td><?php echo e($u->last_name); ?></td>
            <td><?php echo e($u->phone); ?></td>
            <td><?php echo e($u->role); ?></td>
            <td><?php echo e($u->isVip() ? '⭐' : '—'); ?></td>
            <td><?php echo e($u->login_streak); ?></td>
            <td>
                <form method="POST" action="<?php echo e(route('admin.users.role', $u)); ?>" style="display:inline;">
                    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <select name="role">
                        <?php $__currentLoopData = ['superadmin','admin','teacher','student','parent','registered']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r); ?>" <?php if($u->role === $r): echo 'selected'; endif; ?>><?php echo e($r); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit">Змінити</button>
                </form>

                <?php if($u->role === 'teacher'): ?>
                    <form method="POST" action="<?php echo e(route('superadmin.users.toggleTrusted', $u)); ?>" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <button type="submit"><?php echo e($u->is_trusted_teacher ? 'Зняти довіру' : 'Довірений'); ?></button>
                    </form>
                <?php endif; ?>

                <a href="<?php echo e(route('profile.show', $u)); ?>">Профіль</a>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

<?php echo e($users->links()); ?>


<hr>


<h2>Зв'язати батька і дитину</h2>
<form method="POST" action="<?php echo e(route('admin.users.linkParent')); ?>">
    <?php echo csrf_field(); ?>
    <div>
        <label>ID батька</label>
        <input type="number" name="parent_id" required>
    </div>
    <div>
        <label>ID дитини (студента)</label>
        <input type="number" name="child_id" required>
    </div>
    <button type="submit">Зв'язати</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/admin/users.blade.php ENDPATH**/ ?>