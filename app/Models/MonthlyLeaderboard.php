<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyLeaderboard extends Model
{
    protected $fillable = ['user_id', 'year', 'month', 'score', 'rank', 'coins_awarded'];

    public function user() { return $this->belongsTo(User::class); }
}
