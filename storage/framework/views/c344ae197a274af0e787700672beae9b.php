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
            <div><img src="<?php echo e($course->getFirstMediaUrl('cover')); ?>" alt="Обкладинка" class="course-cover"></div>
        <?php endif; ?>
        <input type="file" name="cover" accept="image/*">
    </div>

    <hr>
    <h3>Розклад занять</h3>
    <p class="text-sm text-muted">Змінення розкладу тут не перегенеровує вже існуючі заняття. Щоб додати нові — натисніть «Згенерувати заняття» після збереження.</p>
    <div>
        <label>Дні тижня</label><br>
        <?php $__currentLoopData = [1=>'Пн',2=>'Вт',3=>'Ср',4=>'Чт',5=>'Пт',6=>'Сб',7=>'Нд']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label class="schedule-day-label">
            <input type="checkbox" name="schedule_days[]" value="<?php echo e($num); ?>"
                   <?php if(is_array($course->schedule_days) && in_array($num, $course->schedule_days)): echo 'checked'; endif; ?>>
            <?php echo e($label); ?>

        </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="schedule-time-row">
        <div><label>Початок заняття</label><br>
            <input type="time" name="schedule_start_time" value="<?php echo e($course->schedule_start_time ? substr($course->schedule_start_time,0,5) : ''); ?>"></div>
        <div><label>Кінець заняття</label><br>
            <input type="time" name="schedule_end_time" value="<?php echo e($course->schedule_end_time ? substr($course->schedule_end_time,0,5) : ''); ?>"></div>
        <div>
            <label>Формат</label><br>
            <select name="schedule_mode" id="sched-mode-edit" onchange="toggleSchedLocation('edit',this.value)">
                <option value="online" <?php if(($course->schedule_mode ?? 'online')==='online'): echo 'selected'; endif; ?>>Онлайн</option>
                <option value="offline" <?php if($course->schedule_mode==='offline'): echo 'selected'; endif; ?>>Офлайн</option>
            </select>
        </div>
    </div>
    <div id="sched-loc-edit" class="schedule-loc-block" style="display:<?php echo e($course->schedule_mode==='offline'?'block':'none'); ?>;">
        <div>
            <label>Локація</label><br>
            <select name="schedule_location_id" id="sched-loc-sel-edit" onchange="filterClassrooms('edit',this.value)">
                <option value="">— Оберіть —</option>
                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($loc->id); ?>" <?php if($course->schedule_location_id == $loc->id): echo 'selected'; endif; ?>><?php echo e($loc->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="mt-1">
            <label>Аудиторія</label><br>
            <select name="schedule_classroom_id" id="sched-room-sel-edit">
                <option value="">— Оберіть —</option>
                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $loc->classrooms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($room->id); ?>" data-location="<?php echo e($loc->id); ?>"
                            <?php if($course->schedule_classroom_id == $room->id): echo 'selected'; endif; ?>>
                        <?php echo e($loc->name); ?> — <?php echo e($room->name); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
    </div>

    <button type="submit" class="btn mt-2">Зберегти</button>
</form>

<?php if(!$course->is_template): ?>
<form method="POST" action="<?php echo e(route('teacher.courses.generateLessons', $course)); ?>" class="form-inline">
    <?php echo csrf_field(); ?>
    <button type="submit" class="btn btn-blue">Згенерувати заняття</button>
</form>
<?php if(session('info')): ?>
<p class="text-muted mt-1"><?php echo e(session('info')); ?></p>
<?php endif; ?>
<?php endif; ?>

<script>
function toggleSchedLocation(suffix, val) {
    document.getElementById('sched-loc-' + suffix).style.display = val === 'offline' ? 'block' : 'none';
}
function filterClassrooms(suffix, locationId) {
    const sel = document.getElementById('sched-room-sel-' + suffix);
    Array.from(sel.options).forEach(o => {
        o.style.display = (!o.dataset.location || o.dataset.location == locationId || !locationId) ? '' : 'none';
    });
    sel.value = '';
}
</script>

<form method="POST" action="<?php echo e(route('teacher.courses.duplicate', $course)); ?>"
      onsubmit="this.querySelector('button').disabled = true">
    <?php echo csrf_field(); ?>
    <button type="submit">Скопіювати курс як шаблон</button>
</form>

<?php if(auth()->user()->isAdmin()): ?>

<form method="POST" action="<?php echo e(route('teacher.courses.destroy', $course)); ?>" id="delete-course-form">
    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
    <button type="button" onclick="showDeleteConfirm()">Видалити курс</button>
</form>
<div id="delete-confirm" class="confirm-delete" style="display:none;">
    <p><strong>Видалити курс «<?php echo e($course->title); ?>»?</strong></p>
    <p class="text-sm text-muted">Ця дія незворотна. Введіть назву курсу для підтвердження:</p>
    <input type="text" id="confirm-title" placeholder="<?php echo e($course->title); ?>">
    <div class="confirm-delete__row">
        <button type="button" id="confirm-delete-btn" disabled onclick="document.getElementById('delete-course-form').submit()">Так, видалити</button>
        <button type="button" onclick="hideDeleteConfirm()">Скасувати</button>
    </div>
</div>
<script>
function showDeleteConfirm() { document.getElementById('delete-confirm').style.display = 'block'; }
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

<?php elseif(auth()->user()->isTeacher()): ?>

<?php
    $hasPendingDeletion = \App\Models\DeletionRequest::where('deletable_type', \App\Models\Course::class)
        ->where('deletable_id', $course->id)->pending()->exists();
?>
<?php if($hasPendingDeletion): ?>
<div class="dr-pending">
    <strong>Запит на видалення надіслано</strong>
    <p class="text-sm text-muted">Очікується рішення адміністратора.</p>
</div>
<?php else: ?>
<button type="button" onclick="document.getElementById('del-request-form').style.display='block';this.style.display='none'"
        class="btn btn-danger">
    Видалити курс
</button>
<div id="del-request-form" class="dr-box" style="display:none;">
    <p class="dr-box__title">Запит на видалення курсу</p>
    <form method="POST" action="<?php echo e(route('deletion.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="deletable_type" value="App\Models\Course">
        <input type="hidden" name="deletable_id" value="<?php echo e($course->id); ?>">
        <textarea name="reason" rows="3" placeholder="Причина видалення (необов'язково)..."></textarea>
        <div class="flex-row mt-1">
            <button type="submit" class="btn btn-sm btn-danger">Надіслати запит</button>
            <button type="button" onclick="document.getElementById('del-request-form').style.display='none';this.closest('div').previousElementSibling.style.display=''"
                    class="btn btn-sm btn-ghost">
                Скасувати
            </button>
        </div>
    </form>
</div>
<?php if(session('deletion_requested')): ?>
<p class="text-success mt-1"><?php echo e(session('deletion_requested')); ?></p>
<?php endif; ?>
<?php if(session('deletion_pending')): ?>
<p class="text-warn mt-1"><?php echo e(session('deletion_pending')); ?></p>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<hr>
<h2>Співвикладачі</h2>
<?php if($course->coTeachers->count()): ?>
    <?php $__currentLoopData = $course->coTeachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coTeacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div>
        <?php echo e($coTeacher->last_name); ?> <?php echo e($coTeacher->first_name); ?> (<?php echo e($coTeacher->role); ?>)
        <?php if(auth()->user()->isAdmin()): ?>
        <form method="POST" action="<?php echo e(route('teacher.courses.coTeachers.remove', [$course, $coTeacher])); ?>" class="form-inline">
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
<div class="students-table-wrap <?php echo e($course->students->count() > 10 ? 'students-table-wrap--scrollable' : ''); ?>">
    <table class="data-table">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Статус</th>
                <th>Оплата</th>
                <th>Записаний</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $course->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><a href="<?php echo e(route('profile.show', $student)); ?>"><?php echo e($student->last_name); ?> <?php echo e($student->first_name); ?></a></td>
                <td>
                    <?php switch($student->pivot->status):
                        case ('active'): ?> Активний <?php break; ?>
                        <?php case ('completed'): ?> Завершив <?php break; ?>
                        <?php case ('pending'): ?> Очікує <?php break; ?>
                        <?php default: ?> <?php echo e($student->pivot->status); ?>

                    <?php endswitch; ?>
                </td>
                <td><?php echo e($student->pivot->is_paid ? '✅' : '❌'); ?></td>
                <td><?php echo e($student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('d.m.Y') : '—'); ?></td>
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