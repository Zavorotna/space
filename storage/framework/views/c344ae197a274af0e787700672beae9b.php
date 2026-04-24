<?php $__env->startSection('title', 'Редагування: ' . $course->title); ?>
<?php $__env->startSection('content'); ?>
<h1>Редагування курсу: <?php echo e($course->title); ?></h1>

<form method="POST" action="<?php echo e(route('teacher.courses.update', $course)); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div><label>Назва</label><input type="text" name="title" value="<?php echo e($course->title); ?>" required></div>
    <div><label>Опис</label><textarea name="description"><?php echo e($course->description); ?></textarea></div>
    <div><label>Програма</label><textarea name="program"><?php echo e($course->program); ?></textarea></div>
    <div><label>Ціна</label><input type="number" name="price" step="0.01" value="<?php echo e($course->price); ?>"></div>
    <div><label>Період</label>
        <select name="billing_period">
            <option value="monthly" <?php if($course->billing_period==='monthly'): echo 'selected'; endif; ?>>Щомісячно</option>
            <option value="one_time" <?php if($course->billing_period==='one_time'): echo 'selected'; endif; ?>>Разово</option>
            <option value="per_lesson" <?php if($course->billing_period==='per_lesson'): echo 'selected'; endif; ?>>За заняття</option>
        </select>
    </div>
    <div><label>Статус</label>
        <select name="status">
            <?php $__currentLoopData = ['waiting'=>'Очікування','enrolling'=>'Набір','active'=>'Активний','completed'=>'Завершений']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($k); ?>" <?php if($course->status===$k): echo 'selected'; endif; ?>><?php echo e($v); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div><label>Тип</label>
        <select name="type">
            <option value="group" <?php if($course->type==='group'): echo 'selected'; endif; ?>>Груповий</option>
            <option value="individual" <?php if($course->type==='individual'): echo 'selected'; endif; ?>>Індивідуальний</option>
        </select>
    </div>
    <?php if(auth()->user()->isAdmin()): ?>
    <div>
        <label>Викладач</label>
        <select name="teacher_id">
            <option value="">— не призначено —</option>
            <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($teacher->id); ?>" <?php if($course->teacher_id === $teacher->id): echo 'selected'; endif; ?>>
                    <?php echo e($teacher->last_name); ?> <?php echo e($teacher->first_name); ?> (<?php echo e($teacher->role); ?>)
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <?php endif; ?>
    <div><label>Telegram</label><input type="url" name="telegram_link" value="<?php echo e($course->telegram_link); ?>"></div>
    <div><label>Дата початку</label><input type="date" name="start_date" value="<?php echo e($course->start_date?->format('Y-m-d')); ?>"></div>
    <div><label>Дата закінчення</label><input type="date" name="end_date" value="<?php echo e($course->end_date?->format('Y-m-d')); ?>"></div>
    <div><label><input type="checkbox" name="is_published" value="1" <?php if($course->is_published): echo 'checked'; endif; ?>> Опубліковано</label></div>
    <div>
        <label>Фото</label>
        <?php if($course->getFirstMediaUrl('cover')): ?>
            <div><img src="<?php echo e($course->getFirstMediaUrl('cover')); ?>" alt="Обкладинка" style="max-width:200px; display:block; margin-bottom:6px;"></div>
        <?php endif; ?>
        <input type="file" name="cover" accept="image/*">
    </div>
    <button type="submit">Зберегти</button>
</form>

<form method="POST" action="<?php echo e(route('teacher.courses.duplicate', $course)); ?>"
      onsubmit="this.querySelector('button').disabled = true">
    <?php echo csrf_field(); ?>
    <button type="submit">Скопіювати курс як шаблон</button>
</form>

<?php if(auth()->user()->isSuperAdmin()): ?>
<form method="POST" action="<?php echo e(route('teacher.courses.destroy', $course)); ?>" id="delete-course-form">
    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
    <button type="button" onclick="showDeleteConfirm()">Видалити курс</button>
</form>

<div id="delete-confirm" style="display:none; border:1px solid red; padding:15px; margin-top:10px;">
    <p><strong>Ви впевнені, що хочете видалити курс «<?php echo e($course->title); ?>»?</strong></p>
    <p>Ця дія незворотна. Введіть назву курсу для підтвердження:</p>
    <input type="text" id="confirm-title" placeholder="<?php echo e($course->title); ?>">
    <br><br>
    <button type="button" id="confirm-delete-btn" disabled onclick="document.getElementById('delete-course-form').submit()">Так, видалити</button>
    <button type="button" onclick="hideDeleteConfirm()">Скасувати</button>
</div>

<script>
function showDeleteConfirm() {
    document.getElementById('delete-confirm').style.display = 'block';
}
function hideDeleteConfirm() {
    document.getElementById('delete-confirm').style.display = 'none';
    document.getElementById('confirm-title').value = '';
    document.getElementById('confirm-delete-btn').disabled = true;
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('confirm-title').addEventListener('input', function () {
        document.getElementById('confirm-delete-btn').disabled = this.value !== '<?php echo e(addslashes($course->title)); ?>';
    });
});
</script>
<?php endif; ?>

<hr>
<h2>Співвикладачі</h2>
<?php if($course->coTeachers->count()): ?>
    <?php $__currentLoopData = $course->coTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coTeacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div>
        <?php echo e($coTeacher->last_name); ?> <?php echo e($coTeacher->first_name); ?> (<?php echo e($coTeacher->role); ?>)
        <?php if(auth()->user()->isAdmin()): ?>
        <form method="POST" action="<?php echo e(route('teacher.courses.coTeachers.remove', [$course, $coTeacher])); ?>" style="display:inline;">
            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
            <button type="submit" onclick="return confirm('Видалити співвикладача?')">Видалити</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php else: ?>
    <p>Співвикладачів ще немає.</p>
<?php endif; ?>

<?php if(auth()->user()->isAdmin()): ?>
<form method="POST" action="<?php echo e(route('teacher.courses.coTeachers.add', $course)); ?>">
    <?php echo csrf_field(); ?>
    <select name="user_id" required>
        <option value="">— Оберіть викладача —</option>
        <?php $__currentLoopData = $teachers->filter(fn($t) => $t->id !== $course->teacher_id && !$course->coTeachers->contains($t->id)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->last_name); ?> <?php echo e($teacher->first_name); ?> (<?php echo e($teacher->role); ?>)</option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <button type="submit">Додати співвикладача</button>
</form>
<?php endif; ?>

<hr>
<h2>Студенти (<?php echo e($course->students->count()); ?>)</h2>
<a href="<?php echo e(route('teacher.courses.applications', $course)); ?>">Заявки</a>

<?php if($course->students->count()): ?>
<div style="max-height: <?php echo e($course->students->count() > 10 ? '400px' : 'none'); ?>; overflow-y: <?php echo e($course->students->count() > 10 ? 'auto' : 'visible'); ?>; border: 1px solid #ddd; margin: 10px 0;">
    <table style="width:100%; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Студент</th>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Статус</th>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Оплата</th>
                <th style="padding:6px 8px; text-align:left; border-bottom:1px solid #ddd;">Записаний</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $course->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:6px 8px;"><a href="<?php echo e(route('profile.show', $student)); ?>"><?php echo e($student->last_name); ?> <?php echo e($student->first_name); ?></a></td>
                <td style="padding:6px 8px;">
                    <?php switch($student->pivot->status):
                        case ('active'): ?> Активний <?php break; ?>
                        <?php case ('completed'): ?> Завершив <?php break; ?>
                        <?php case ('pending'): ?> Очікує <?php break; ?>
                        <?php default: ?> <?php echo e($student->pivot->status); ?>

                    <?php endswitch; ?>
                </td>
                <td style="padding:6px 8px;"><?php echo e($student->pivot->is_paid ? '✅' : '❌'); ?></td>
                <td style="padding:6px 8px;"><?php echo e($student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d.m.Y') : '—'); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>Студентів ще немає.</p>
<?php endif; ?>

<h3>Додати студента напряму</h3>
<form method="POST" action="<?php echo e(route('teacher.courses.addStudent', $course)); ?>">
    <?php echo csrf_field(); ?>
    <input type="number" name="user_id" placeholder="ID студента" required>
    <button type="submit">Додати</button>
</form>

<hr>
<h2>Домашні завдання</h2>
<?php $__currentLoopData = $course->homeworkAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($hw->title); ?></strong> (<?php echo e($hw->difficulty); ?>, <?php echo e($hw->reward_coins); ?> монет)
    — до <?php echo e($hw->deadline->format('d.m.Y')); ?>

    <a href="<?php echo e(route('teacher.homework.submissions', $hw)); ?>">Здачі (<?php echo e($hw->submissions->count()); ?>)</a>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<h3>Нове завдання</h3>
<form method="POST" action="<?php echo e(route('teacher.homework.store', $course)); ?>">
    <?php echo csrf_field(); ?>
    <input type="text" name="title" placeholder="Назва" required>
    <textarea name="description" placeholder="Опис"></textarea>
    <select name="difficulty">
        <option value="easy">Легка (5 монет)</option>
        <option value="medium" selected>Середня (15 монет)</option>
        <option value="hard">Важка (25 монет)</option>
    </select>
    <input type="date" name="deadline" required>
    <button type="submit">Додати</button>
</form>

<hr>
<h2>Тести</h2>
<?php $__currentLoopData = $course->tests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div>
    <strong><?php echo e($test->title); ?></strong> (<?php echo e($test->questions->count()); ?> питань)
    <a href="<?php echo e(route('teacher.tests.edit', $test)); ?>">Редагувати</a>
    <a href="<?php echo e(route('teacher.tests.statistics', $test)); ?>">Статистика</a>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<h3>Новий тест</h3>
<form method="POST" action="<?php echo e(route('teacher.tests.store', $course->id)); ?>">
    <?php echo csrf_field(); ?>
    <input type="text" name="title" placeholder="Назва тесту" required>
    <textarea name="description" placeholder="Опис"></textarea>
    <input type="number" name="passing_score" value="60" min="1" max="100">% прохідний бал
    <button type="submit">Створити</button>
</form>

<hr>
<h2>Випускний проєкт</h2>
<?php if($course->graduationProject): ?>
    <p><?php echo e($course->graduationProject->title); ?> — до <?php echo e($course->graduationProject->deadline->format('d.m.Y')); ?></p>
    <a href="<?php echo e(route('teacher.graduation.submissions', $course->graduationProject)); ?>">Здачі</a>
<?php else: ?>
    <form method="POST" action="<?php echo e(route('teacher.graduation.store', $course)); ?>">
        <?php echo csrf_field(); ?>
        <input type="text" name="title" placeholder="Назва проєкту" required>
        <textarea name="description" placeholder="Опис"></textarea>
        <input type="date" name="deadline" required>
        <button type="submit">Створити</button>
    </form>
<?php endif; ?>

<hr>
<h2>Додаткові матеріали</h2>
<?php $__currentLoopData = $course->additionalMaterials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div><?php echo e($mat->title); ?> — <?php echo e($mat->price_coins); ?> монет</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<form method="POST" action="<?php echo e(route('teacher.materials.store', $course)); ?>">
    <?php echo csrf_field(); ?>
    <input type="text" name="title" placeholder="Назва" required>
    <textarea name="description" placeholder="Опис"></textarea>
    <input type="url" name="url" placeholder="Посилання">
    <input type="number" name="price_coins" value="0" min="0"> монет
    <button type="submit">Додати</button>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/teacher/course-edit.blade.php ENDPATH**/ ?>