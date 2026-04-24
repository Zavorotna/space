<?php $__env->startSection('title', 'Оплата курсу: ' . $course->title); ?>

<?php $__env->startSection('content'); ?>
<a href="<?php echo e(route('courses.student.show', $course)); ?>">&larr; Назад</a>

<h1>Оплата курсу</h1>

<div>
    <h2><?php echo e($course->title); ?></h2>
    <p>Ціна: <?php echo e($course->price); ?> грн/міс</p>

    <?php if($discount > 0): ?>
        <p>Знижка (сертифікат): -<?php echo e($discount); ?>%</p>
    <?php endif; ?>

    <?php if(auth()->user()->isVip()): ?>
        <p>VIP знижка: -5%</p>
    <?php endif; ?>

    <p><strong>До сплати: <?php echo e($finalPrice); ?> грн</strong></p>
</div>

<h3>Оплата карткою (LiqPay)</h3>
<form method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
    <input type="hidden" name="data" value="<?php echo e($paymentData['data'] ?? ''); ?>">
    <input type="hidden" name="signature" value="<?php echo e($paymentData['signature'] ?? ''); ?>">
    <button type="submit">Оплатити карткою — <?php echo e($finalPrice); ?> грн</button>
</form>

<hr>

<h3>Або оплата монетами</h3>
<?php $wallet = auth()->user()->getOrCreateWallet(); ?>
<p>Ваш баланс: <?php echo e($wallet->balance); ?> монет</p>
<?php if($wallet->balance >= $finalPrice): ?>
    <form method="POST" action="<?php echo e(route('courses.pay.process', $course)); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit">Оплатити монетами — <?php echo e($finalPrice); ?> монет</button>
    </form>
<?php else: ?>
    <p>Недостатньо монет. <a href="<?php echo e(route('wallet.topup')); ?>">Поповнити</a></p>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/student/course-pay.blade.php ENDPATH**/ ?>