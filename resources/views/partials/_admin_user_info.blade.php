@if(auth()->check() && auth()->user()->isAdmin())
<div class="card-panel mt-2 flex-row">
    <span>◈ Хештег коіни:</span>
    <strong>{{ $user->wallet?->balance ?? 0 }}</strong>
    @if($user->isVip())
    <span class="text-muted text-sm">· VIP до {{ $user->vip_expires_at?->format('d.m.Y') }}</span>
    @endif
</div>
@endif