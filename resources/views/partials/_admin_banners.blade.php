@if(!empty($adminBanners) && $adminBanners->count())
<div id="admin-banners" style="margin-bottom:16px;">
    @foreach($adminBanners as $banner)
    <div id="banner-{{ $banner->id }}"
         style="display:flex;align-items:flex-start;gap:12px;background:#fff8e1;border:1px solid #f5a623;border-left:4px solid #f5a623;border-radius:6px;padding:12px 14px;margin-bottom:8px;">
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
    @endforeach
</div>

@once
<script>
function dismissBanner(id) {
    const el = document.getElementById('banner-' + id);
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/notifications/' + id + '/dismiss', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
    }).then(() => {
        if (el) {
            el.style.transition = 'opacity .25s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 260);
        }
    });
}
</script>
@endonce
@endif