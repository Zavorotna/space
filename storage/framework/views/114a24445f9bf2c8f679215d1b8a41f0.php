<?php $__env->startSection('title', 'Редагування резюме'); ?>

<?php $__env->startSection('content'); ?>
<h1>Редагування резюме</h1>

<form method="POST" action="<?php echo e(route('resume.update')); ?>">
    <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

    <div>
        <label>Про себе</label>
        <textarea name="about" rows="4"><?php echo e(old('about', $resume->about)); ?></textarea>
    </div>

    <div>
        <label>Досвід роботи</label>
        <textarea name="work_experience" rows="4"><?php echo e(old('work_experience', $resume->work_experience)); ?></textarea>
    </div>

    <div>
        <label>Посилання на проєкти (кожне з нового рядка)</label>
        <textarea name="project_links_text" rows="3" id="project-links"><?php echo e(old('project_links_text', $resume->project_links ? implode("\n", $resume->project_links) : '')); ?></textarea>
    </div>

    <div>
        <label>Email для зв'язку</label>
        <input type="email" name="contact_email" value="<?php echo e(old('contact_email', $resume->contact_email)); ?>">
    </div>

    <div>
        <label>Телефон для зв'язку</label>
        <input type="text" name="contact_phone" value="<?php echo e(old('contact_phone', $resume->contact_phone)); ?>">
    </div>

    
    <?php if($courses->count()): ?>
    <div>
        <h3>Курси у резюме</h3>
        <p>Зніміть галочку, щоб приховати невдалий курс:</p>
        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <label>
                <input type="checkbox" name="hidden_courses[]" value="<?php echo e($course->id); ?>"
                    <?php if(in_array($course->id, $resume->hidden_courses ?? [])): echo 'checked'; endif; ?>>
                Приховати: <?php echo e($course->title); ?>

            </label><br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div>
        <label>
            <input type="checkbox" name="has_offer" value="1" <?php if($resume->has_offer): echo 'checked'; endif; ?>>
            Отримав офер (вимкнути публікацію)
        </label>
    </div>

    <button type="submit">Зберегти</button>
</form>

<hr>


<h2>Публікація</h2>
<?php if($resume->is_published): ?>
    <p>Резюме опубліковано. <a href="<?php echo e(route('resumes.show', $resume)); ?>">Переглянути</a></p>
<?php else: ?>
    <form method="POST" action="<?php echo e(route('resume.publish')); ?>">
        <?php echo csrf_field(); ?>
        <?php if(auth()->user()->isVip()): ?>
            <p>Публікація безкоштовна (VIP)</p>
        <?php else: ?>
            <p>Вартість: 100 монет на 1 рік</p>
        <?php endif; ?>
        <button type="submit">Опублікувати резюме</button>
    </form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/resume/edit.blade.php ENDPATH**/ ?>