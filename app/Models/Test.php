<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'passing_score', 'sort_order'];

    public function course() { return $this->belongsTo(Course::class); }
    public function questions() { return $this->hasMany(TestQuestion::class)->orderBy('sort_order'); }
    public function attempts() { return $this->hasMany(TestAttempt::class); }

    public function userHasPassed(int $userId): bool
    {
        return $this->attempts()->where('user_id', $userId)->where('passed', true)->exists();
    }

    public function userAttemptCount(int $userId): int
    {
        return $this->attempts()->where('user_id', $userId)->count();
    }
}
