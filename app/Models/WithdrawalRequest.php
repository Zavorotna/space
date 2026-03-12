<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = ['user_id', 'amount', 'status', 'pickup_note', 'processed_by', 'processed_at'];
    protected function casts(): array { return ['processed_at' => 'datetime']; }

    public function user() { return $this->belongsTo(User::class); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }

    public static function isValidAmount(int $amount): bool
    {
        return $amount >= 100 && $amount % 100 === 0;
    }
}
