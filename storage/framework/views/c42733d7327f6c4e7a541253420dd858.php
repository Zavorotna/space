<?php $__env->startSection('title', $test->title); ?>
<?php $__env->startSection('content'); ?>
<h1>Тест №<?php echo e($test->sort_order + 1); ?></h1>
<h2><?php echo e($test->title); ?></h2>

<?php if($test->description): ?>
<div><p><?php echo e($test->description); ?></p></div>
<?php endif; ?>

<?php if(isset($attempt)): ?>
<form method="POST" action="<?php echo e(route('tests.submit', $attempt)); ?>" id="test-form">
    <?php echo csrf_field(); ?>
    <?php $__currentLoopData = $test->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div>
        <p><strong><?php echo e($i + 1); ?>. <?php echo e($question->text); ?></strong></p>
        <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div>
            <?php if($question->type === 'single'): ?>
                <label>
                    <input type="radio" name="answers[<?php echo e($question->id); ?>]" value="<?php echo e($option->id); ?>">
                    <?php echo e($option->text); ?>

                </label>
            <?php else: ?>
                <label>
                    <input type="checkbox" name="answers[<?php echo e($question->id); ?>][]" value="<?php echo e($option->id); ?>">
                    <?php echo e($option->text); ?>

                </label>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($question->hint): ?>
        <div>
            <button type="button" class="hint-btn" data-question-id="<?php echo e($question->id); ?>">Підказка (15 монет)</button>
            <span class="hint-text" id="hint-<?php echo e($question->id); ?>" style="display:none"></span>
            <input type="hidden" name="hints_used[]" class="hint-input" disabled>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <button type="submit">Завершити</button>
</form>
<?php else: ?>
    <?php if($attemptCount > 0): ?>
        <p>Спроба #<?php echo e($attemptCount + 1); ?>. Вартість: 10 монет.</p>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('tests.start', $test)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit">Почати тест</button>
    </form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.querySelectorAll('.hint-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const qId = this.dataset.questionId;
        fetch(`/test-questions/${qId}/hint`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.hint) {
                document.getElementById('hint-' + qId).textContent = data.hint;
                document.getElementById('hint-' + qId).style.display = 'block';
                this.disabled = true;
                // Enable hidden input to track hint usage
                const input = this.parentElement.querySelector('.hint-input');
                input.value = qId;
                input.disabled = false;
            } else if (data.error) {
                alert(data.error);
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/test/take.blade.php ENDPATH**/ ?>