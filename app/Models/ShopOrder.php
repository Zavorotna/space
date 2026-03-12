<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopOrder extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'quantity', 'payment_method',
        'total_coins', 'total_uah', 'status', 'liqpay_order_id',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(ShopProduct::class, 'product_id'); }
}
