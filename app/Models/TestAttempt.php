<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAttempt extends Model
{
    protected $fillable = [
        'test_id', 'user_id', 'attempt_number', 'score', 'passed',
        'coins_awarded', 'hints_used', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function test() { return $this->belongsTo(Test::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function answers() { return $this->hasMany(TestAttemptAnswer::class, 'attempt_id'); }
}
