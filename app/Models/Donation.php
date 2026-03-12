<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donation extends Model
{
    protected $fillable = ['user_id', 'amount', 'total_donated'];

    public function user() { return $this->belongsTo(User::class); }
}
