<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'commission_amount', 'description',
        'reference_type', 'reference_id', 'related_user_id', 'status',
        'admin_note', 'liqpay_order_id', 'liqpay_status',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function relatedUser() { return $this->belongsTo(User::class, 'related_user_id'); }

    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            return $this->morphTo('reference', 'reference_type', 'reference_id');
        }
        return null;
    }

    public function scopeCredits($query) { return $query->where('amount', '>', 0); }
    public function scopeDebits($query) { return $query->where('amount', '<', 0); }
}
