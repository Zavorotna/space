@if(!empty($adminBanners) && $adminBanners->count())
<div class="admin-banners">
    @foreach($adminBanners as $banner)
    <div id="banner-{{ $banner->id }}"
         class="admin-banner admin-banner--{{ $banner->type === 'deletion_request' ? 'deletion' : 'message' }}">

        @if($banner->type === 'deletion_request' && $banner->deletionRequest)
        @php $dr = $banner->deletionRequest; $deletable = $dr->deletable; @endphp
        <div class="admin-banner__body">
            <div class="admin-banner__content">
                <strong class="admin-banner__title admin-banner__title--deletion">🗑 {{ $banner->title }}</strong>
                <p class="admin-banner__text">{{ $banner->message }}</p>
                @if($deletable)
                <p class="admin-banner__meta">
                    Тип: {{ class_basename($dr->deletable_type) }}
                    @if($deletable->type ?? false) · {{ $deletable->type }} @endif
                </p>
                @endif
                <span class="admin-banner__ts">{{ $banner->created_at->translatedFormat('d F Y, H:i') }}</span>
                <div class="admin-banner__actions">
                    <button onclick="drAction({{ $dr->id }}, 'approve', {{ $banner->id }})"
                            class="btn btn-sm btn-success">
                        Підтвердити видалення
                    </button>
                    <button onclick="drAction({{ $dr->id }}, 'reject', {{ $banner->id }})"
                            class="btn btn-sm btn-warn">
                        Відхилити
                    </button>
                    @if(auth()->user()->isSuperAdmin())
                    <button onclick="drDestroy({{ $dr->id }}, {{ $banner->id }})"
                            class="btn btn-sm btn-muted">
                        Видалити назавжди
                    </button>
                    @endif
                </div>
            </div>
        </div>

        @else
        <div class="admin-banner__body">
            <div class="admin-banner__content">
                <strong class="admin-banner__title">{{ $banner->title }}</strong>
                @if($banner->message)
                <p class="admin-banner__text">{{ $banner->message }}</p>
                @endif
                <span class="admin-banner__ts">{{ $banner->created_at->translatedFormat('d F Y, H:i') }}</span>
            </div>
            <button onclick="dismissBanner({{ $banner->id }})"
                    title="Закрити"
                    class="admin-banner__dismiss">✕</button>
        </div>
        @endif
    </div>
    @endforeach
</div>

@once
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
@endonce
@endif