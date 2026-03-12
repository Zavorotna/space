<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = ['referrer_id', 'referred_id', 'course_id', 'rewarded'];
    protected function casts(): array { return ['rewarded' => 'boolean']; }

    public function referrer() { return $this->belongsTo(User::class, 'referrer_id'); }
    public function referred() { return $this->belongsTo(User::class, 'referred_id'); }
    public function course() { return $this->belongsTo(Course::class); }
}
