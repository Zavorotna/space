<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['slug', 'title', 'description', 'reward_coins', 'icon', 'type', 'threshold'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot(['coins_awarded', 'earned_at'])->withTimestamps();
    }
}
