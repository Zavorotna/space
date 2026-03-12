<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusUsage extends Model
{
    protected $fillable = ['inventory_id', 'user_id', 'used_on_type', 'used_on_id'];

    public function inventory() { return $this->belongsTo(BonusInventory::class, 'inventory_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
