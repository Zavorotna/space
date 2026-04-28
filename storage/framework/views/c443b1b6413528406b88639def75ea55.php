<?php $__env->startSection('title', 'Редагування тесту: ' . $test->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('teacher.courses.edit', $test->course_id)); ?>">&larr; Назад до курсу</a>

<h1>Редагування тесту</h1>

<form method="POST" action="<?php echo e(route('teacher.tests.update', $test)); ?>">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div>
        <label>Назва тесту</label>
        <input type="text" name="title" value="<?php echo e(old('title', $test->title)); ?>" required>
    </div>
    <div>
        <label>Опис</label>
        <textarea name="description"><?php echo e(old('description', $test->description)); ?></textarea>
    </div>
    <div>
        <label>Прохідний бал (%)</label>
        <input type="number" name="passing_score" value="<?php echo e(old('passing_score', $test->passing_score)); ?>" min="1" max="100" required>
    </div>
    <button type="submit">Зберегти</button>
</form>

<?php if(auth()->user()->isAdmin()): ?>
<form method="POST" action="<?php echo e(route('teacher.tests.destroy', $test)); ?>" id="delete-test-form" class="mt-2">
    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
    <button type="button" onclick="showTestDeleteConfirm()">Видалити тест</button>
</form>
<div id="test-delete-confirm" class="confirm-delete" style="display:none;">
    <p><strong>Видалити тест «<?php echo e($test->title); ?>»?</strong></p>
    <p class="text-sm text-muted">Буде видалено <?php echo e($test->questions->count()); ?> питань та всі результати студентів. Введіть назву тесту:</p>
    <input type="text" id="delete-test-input" placeholder="<?php echo e($test->title); ?>">
    <div class="confirm-delete__row">
        <button type="button" id="confirm-delete-test-btn" disabled
                onclick="document.getElementById('delete-test-form').submit()">Так, видалити</button>
        <button type="button" onclick="hideTestDeleteConfirm()">Скасувати</button>
    </div>
</div>
<script>
function showTestDeleteConfirm() { document.getElementById('test-delete-confirm').style.display = 'block'; }
function hideTestDeleteConfirm() {
    document.getElementById('test-delete-confirm').style.display = 'none';
    document.getElementById('delete-test-input').value = '';
    document.getElementById('confirm-delete-test-btn').disabled = true;
}
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('delete-test-input').addEventListener('input', function () {
        document.getElementById('confirm-delete-test-btn').disabled = this.value !== '<?php echo e(addslashes($test->title)); ?>';
    });
});
</script>

<?php elseif(auth()->user()->isTeacher()): ?>
<?php
    $hasPendingTestDeletion = \App\Models\DeletionRequest::where('deletable_type', \App\Models\Test::class)
        ->where('deletable_id', $test->id)->pending()->exists();
?>
<?php if($hasPendingTestDeletion): ?>
<div class="dr-pending">
    <strong>Запит на видалення надіслано</strong>
    <p class="text-sm text-muted">Очікується рішення адміністратора.</p>
</div>
<?php else: ?>
<button type="button" onclick="document.getElementById('del-test-request-form').style.display='block';this.style.display='none'"
        class="btn btn-danger mt-2">
    Видалити тест
</button>
<div id="del-test-request-form" class="dr-box" style="display:none;">
    <p class="dr-box__title">Запит на видалення тесту</p>
    <form method="POST" action="<?php echo e(route('deletion.store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="deletable_type" value="App\Models\Test">
        <input type="hidden" name="deletable_id" value="<?php echo e($test->id); ?>">
        <textarea name="reason" rows="3" placeholder="Причина видалення (необов'язково)..."></textarea>
        <div class="flex-row mt-1">
            <button type="submit" class="btn btn-sm btn-danger">Надіслати запит</button>
            <button type="button" onclick="document.getElementById('del-test-request-form').style.display='none'"
                    class="btn btn-sm btn-ghost">
                Скасувати
            </button>
        </div>
    </form>
</div>
<?php if(session('deletion_requested')): ?>
<p class="text-success mt-1"><?php echo e(session('deletion_requested')); ?></p>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<hr>

<h2>Питання (<?php echo e($test->questions->count()); ?>)</h2>

<?php $__currentLoopData = $test->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="question-card">
    <form method="POST" action="<?php echo e(route('teacher.tests.updateQuestion', $question)); ?>">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <strong>Питання <?php echo e($index + 1); ?></strong>
        <div>
            <label>Текст питання</label>
            <textarea name="text" required><?php echo e($question->text); ?></textarea>
        </div>
        <div>
            <label>Тип</label>
            <select name="type">
                <option value="single" <?php if($question->type === 'single'): echo 'selected'; endif; ?>>Одна правильна</option>
                <option value="multiple" <?php if($question->type === 'multiple'): echo 'selected'; endif; ?>>Декілька правильних</option>
            </select>
        </div>
        <div>
            <label>Підказка (необов'язково)</label>
            <textarea name="hint"><?php echo e($question->hint); ?></textarea>
        </div>

        <h4>Варіанти відповідей</h4>
        <div id="options-<?php echo e($question->id); ?>">
            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oi => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div>
                <input type="hidden" name="options[<?php echo e($oi); ?>][id]" value="<?php echo e($option->id); ?>">
                <input type="text" name="options[<?php echo e($oi); ?>][text]" value="<?php echo e($option->text); ?>" required>
                <label>
                    <input type="checkbox" name="options[<?php echo e($oi); ?>][is_correct]" value="1"
                        <?php if($option->is_correct): echo 'checked'; endif; ?>>
                    Правильна
                </label>
                <input type="hidden" name="options[<?php echo e($oi); ?>][is_correct]" value="0" class="fallback-correct">
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <button type="submit">Оновити питання</button>
    </form>

    <form method="POST" action="<?php echo e(route('teacher.tests.deleteQuestion', $question)); ?>" class="form-inline"
          onsubmit="return confirm('Видалити це питання?')">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button type="submit">Видалити питання</button>
    </form>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<hr>

<h2>Додати нове питання</h2>
<form method="POST" action="<?php echo e(route('teacher.tests.addQuestion', $test)); ?>" id="new-question-form">
    <?php echo csrf_field(); ?>
    <div>
        <label>Текст питання</label>
        <textarea name="text" required></textarea>
    </div>
    <div>
        <label>Тип</label>
        <select name="type">
            <option value="single">Одна правильна</option>
            <option value="multiple">Декілька правильних</option>
        </select>
    </div>
    <div>
        <label>Підказка</label>
        <textarea name="hint"></textarea>
    </div>

    <h4>Варіанти відповідей</h4>
    <div id="new-options">
        <div>
            <input type="text" name="options[0][text]" placeholder="Варіант 1" required>
            <label><input type="checkbox" name="options[0][is_correct]" value="1"> Правильна</label>
            <input type="hidden" name="options[0][is_correct]" value="0" class="fallback-correct">
        </div>
        <div>
            <input type="text" name="options[1][text]" placeholder="Варіант 2" required>
            <label><input type="checkbox" name="options[1][is_correct]" value="1"> Правильна</label>
            <input type="hidden" name="options[1][is_correct]" value="0" class="fallback-correct">
        </div>
    </div>
    <button type="button" onclick="addOption()">+ Додати варіант</button>
    <br><br>
    <button type="submit">Додати питання</button>
</form>

<script>
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        this.querySelectorAll('.fallback-correct').forEach(hidden => {
            const checkbox = hidden.previousElementSibling?.querySelector('input[type="checkbox"]')
                || hidden.parentElement.querySelector('input[type="checkbox"]');
            if (checkbox && checkbox.checked) {
                hidden.disabled = true;
            }
        });
    });
});

let optionCount = 2;
function addOption() {
    const container = document.getElementById('new-options');
    const div = document.createElement('div');
    div.innerHTML = `
        <input type="text" name="options[${optionCount}][text]" placeholder="Варіант ${optionCount + 1}" required>
        <label><input type="checkbox" name="options[${optionCount}][is_correct]" value="1"> Правильна</label>
        <input type="hidden" name="options[${optionCount}][is_correct]" value="0" class="fallback-correct">
    `;
    container.appendChild(div);
    optionCount++;
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/test/edit.blade.php ENDPATH**/ ?>