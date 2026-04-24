<?php $__env->startSection('title', 'Оплата'); ?>
<?php $__env->startSection('content'); ?>
<h2>Перенаправлення на LiqPay...</h2>
<form id="liqpay-form" method="POST" action="https://www.liqpay.ua/api/3/checkout" accept-charset="utf-8">
    <input type="hidden" name="data" value="<?php echo e($paymentData['data']); ?>">
    <input type="hidden" name="signature" value="<?php echo e($paymentData['signature']); ?>">
    <button type="submit">Перейти до оплати</button>
</form>
<script>document.getElementById('liqpay-form').submit();</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/wallet/liqpay-redirect.blade.php ENDPATH**/ ?>