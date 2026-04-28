@if(!empty($adminBanners) && $adminBanners->count())
<div id="admin-banners" style="margin-bottom:16px;">
    @foreach($adminBanners as $banner)
    <div id="banner-{{ $banner->id }}"
         style="background:{{ $banner->type === 'deletion_request' ? '#fdecea' : '#fff8e1' }};
                border:1px solid {{ $banner->type === 'deletion_request' ? '#e74c3c' : '#f5a623' }};
                border-left:4px solid {{ $banner->type === 'deletion_request' ? '#e74c3c' : '#f5a623' }};
                border-radius:6px;padding:12px 14px;margin-bottom:8px;">

        @if($banner->type === 'deletion_request' && $banner->deletionRequest)
        @php $dr = $banner->deletionRequest; $deletable = $dr->deletable; @endphp
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <div style="flex:1;min-width:0;">
                <strong style="font-size:.9rem;color:#c0392b;">🗑 {{ $banner->title }}</strong>
                <p style="margin:4px 0 0;font-size:.88rem;color:#555;">{{ $banner->message }}</p>
                @if($deletable)
                <p style="margin:4px 0 0;font-size:.78rem;color:#888;">
                    Тип: {{ class_basename($dr->deletable_type) }}
                    @if($deletable->type ?? false) · {{ $deletable->type }} @endif
                </p>
                @endif
                <span style="font-size:.75rem;color:#aaa;">{{ $banner->created_at->translatedFormat('d F Y, H:i') }}</span>
                <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                    <button onclick="drAction({{ $dr->id }}, 'approve', {{ $banner->id }})"
                            style="padding:5px 12px;background:#27ae60;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:.82rem;">
                        Підтвердити видалення
                    </button>
                    <button onclick="drAction({{ $dr->id }}, 'reject', {{ $banner->id }})"
                            style="padding:5px 12px;background:#e67e22;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:.82rem;">
                        Відхилити
                    </button>
                    @if(auth()->user()->isSuperAdmin())
                    <button onclick="drDestroy({{ $dr->id }}, {{ $banner->id }})"
                            style="padding:5px 12px;background:#95a5a6;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:.82rem;">
                        Видалити назавжди
                    </button>
                    @endif
                </div>
            </div>
        </div>

        @else
        {{-- Regular admin_message banner --}}
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <div style="flex:1;min-width:0;">
                <strong style="font-size:.9rem;">{{ $banner->title }}</strong>
                @if($banner->message)
                <p style="margin:4px 0 0;font-size:.88rem;color:#555;white-space:pre-wrap;">{{ $banner->message }}</p>
                @endif
                <span style="font-size:.75rem;color:#aaa;">{{ $banner->created_at->translatedFormat('d F Y, H:i') }}</span>
            </div>
            <button onclick="dismissBanner({{ $banner->id }})"
                    title="Закрити"
                    style="background:none;border:none;cursor:pointer;color:#aaa;font-size:1.1rem;padding:0 2px;line-height:1;flex-shrink:0;">✕</button>
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