<?php

namespace App\Services;

use App\Models\User;

class VipService
{
    public function grantVip(User $user, string $reason = ''): void
    {
        $user->update([
            'is_vip' => true,
            'vip_expires_at' => now()->addMonths(3),
        ]);

        // VIP includes resume placement
        if ($user->resume) {
            $user->resume->update(['is_published' => true]);
            $user->update(['resume_expires_at' => $user->vip_expires_at]);
        }
    }

    public function purchaseVip(User $user): void
    {
        app(WalletService::class)->deduct($user, 500, 'vip_purchase', 'VIP статус на 3 місяці');
        $this->grantVip($user, 'Придбано за 500 монет');
    }

    public function getVipDiscount(User $user, string $type): float
    {
        if (!$user->isVip()) return 0;
        return match($type) {
            'course' => 5,      // 5% на курси
            'bonus' => 10,      // 10% на бонуси
            default => 0,
        };
    }

    public function checkExpiration(User $user): void
    {
        if ($user->is_vip && $user->vip_expires_at && $user->vip_expires_at->isPast()) {
            $user->update(['is_vip' => false, 'vip_expires_at' => null]);
        }
    }
}
