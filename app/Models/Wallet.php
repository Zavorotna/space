<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance'];

    public function user() { return $this->belongsTo(User::class); }

    /**
     * Credit coins to wallet (atomic)
     */
    public function credit(int $amount): bool
    {
        if ($amount <= 0) return false;
        return DB::table('wallets')->where('id', $this->id)
            ->increment('balance', $amount) > 0;
    }

    /**
     * Debit coins from wallet (atomic, checks balance)
     */
    public function debit(int $amount): bool
    {
        if ($amount <= 0) return false;
        $affected = DB::table('wallets')
            ->where('id', $this->id)
            ->where('balance', '>=', $amount)
            ->decrement('balance', $amount);
        return $affected > 0;
    }

    /**
     * Transfer with 1% commission to platform
     */
    public function transferTo(Wallet $recipient, int $amount): bool
    {
        if ($amount <= 0) return false;
        $commission = max(1, (int) ceil($amount * 0.01));
        $total = $amount + $commission;

        return DB::transaction(function () use ($recipient, $amount, $commission, $total) {
            if (!$this->debit($total)) return false;
            $recipient->credit($amount);
            return true;
        });
    }
}
