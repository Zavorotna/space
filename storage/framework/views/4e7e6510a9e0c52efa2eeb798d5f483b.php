<?php $__env->startSection('title', 'Hashtag Space — IT Academy'); ?>
<?php $__env->startSection('content'); ?>
<h1>HASHTAG SPACE</h1>
<p>IT Academy Hashtag — онлайн та офлайн курси</p>
<a href="<?php echo e(route('register')); ?>">Зареєструватися</a>
<a href="<?php echo e(route('login')); ?>">Увійти</a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/user/Documents/project/space/resources/views/public/home.blade.php ENDPATH**/ ?>