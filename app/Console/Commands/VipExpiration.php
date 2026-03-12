<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\VipService;
use Illuminate\Console\Command;

/**
 * Check and expire VIP statuses.
 * Run daily: 0 0 * * * php artisan vip:check
 */
class VipExpiration extends Command
{
    protected $signature = 'vip:check';
    protected $description = 'Check and expire VIP statuses';

    public function handle(VipService $vip): void
    {
        $expired = User::where('is_vip', true)
            ->where('vip_expires_at', '<', now())
            ->get();

        foreach ($expired as $user) {
            $vip->checkExpiration($user);
            $this->info("VIP expired: {$user->full_name}");
        }
    }
}
