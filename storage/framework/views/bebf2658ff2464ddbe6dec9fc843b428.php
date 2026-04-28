<?php if(!empty($adminBanners) && $adminBanners->count()): ?>
<div class="admin-banners">
    <?php $__currentLoopData = $adminBanners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div id="banner-<?php echo e($banner->id); ?>"
         class="admin-banner admin-banner--<?php echo e($banner->type === 'deletion_request' ? 'deletion' : 'message'); ?>">

        <?php if($banner->type === 'deletion_request' && $banner->deletionRequest): ?>
        <?php $dr = $banner->deletionRequest; $deletable = $dr->deletable; ?>
        <div class="admin-banner__body">
            <div class="admin-banner__content">
                <strong class="admin-banner__title admin-banner__title--deletion">🗑 <?php echo e($banner->title); ?></strong>
                <p class="admin-banner__text"><?php echo e($banner->message); ?></p>
                <?php if($deletable): ?>
                <p class="admin-banner__meta">
                    Тип: <?php echo e(class_basename($dr->deletable_type)); ?>

                    <?php if($deletable->type ?? false): ?> · <?php echo e($deletable->type); ?> <?php endif; ?>
                </p>
                <?php endif; ?>
                <span class="admin-banner__ts"><?php echo e($banner->created_at->translatedFormat('d F Y, H:i')); ?></span>
                <div class="admin-banner__actions">
                    <button onclick="drAction(<?php echo e($dr->id); ?>, 'approve', <?php echo e($banner->id); ?>)"
                            class="btn btn-sm btn-success">
                        Підтвердити видалення
                    </button>
                    <button onclick="drAction(<?php echo e($dr->id); ?>, 'reject', <?php echo e($banner->id); ?>)"
                            class="btn btn-sm btn-warn">
                        Відхилити
                    </button>
                    <?php if(auth()->user()->isSuperAdmin()): ?>
                    <button onclick="drDestroy(<?php echo e($dr->id); ?>, <?php echo e($banner->id); ?>)"
                            class="btn btn-sm btn-muted">
                        Видалити назавжди
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="admin-banner__body">
            <div class="admin-banner__content">
                <strong class="admin-banner__title"><?php echo e($banner->title); ?></strong>
                <?php if($banner->message): ?>
                <p class="admin-banner__text"><?php echo e($banner->message); ?></p>
                <?php endif; ?>
                <span class="admin-banner__ts"><?php echo e($banner->created_at->translatedFormat('d F Y, H:i')); ?></span>
            </div>
            <button onclick="dismissBanner(<?php echo e($banner->id); ?>)"
                    title="Закрити"
                    class="admin-banner__dismiss">✕</button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if (! $__env->hasRenderedOnce('487ce937-ea62-4d40-9657-e5980725bbe3')): $__env->markAsRenderedOnce('487ce937-ea62-4d40-9657-e5980725bbe3'); ?>
<script>
const _csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function _removeBanner(id) {
    const el = document.getElementById('banner-' + id);
    if (!el) return;
    el.style.transition = 'opacity .25s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 260);
}

function dismissBanner(id) {
    fetch('/notifications/' + id + '/dismiss', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
    }).then(() => _removeBanner(id));
}

function drAction(drId, action, bannerId) {
    fetch('/deletion-requests/' + drId + '/' + action, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => {
        if (data.ok) _removeBanner(bannerId);
        else alert(data.error ?? 'Помилка');
    });
}

function drDestroy(drId, bannerId) {
    if (!confirm('Видалити запит назавжди? Сповіщення зникне у всіх адмінів.')) return;
    fetch('/deletion-requests/' + drId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => {
        if (data.ok) _removeBanner(bannerId);
        else alert(data.error ?? 'Помилка');
    });
}
</script>
<?php endif; ?>
<?php endif; ?><?php /**PATH /Users/dmitropirizok/projects/hashtag-space-lms/resources/views/partials/_admin_banners.blade.php ENDPATH**/ ?>