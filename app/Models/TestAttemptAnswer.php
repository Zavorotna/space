<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestAttemptAnswer extends Model
{
    protected $fillable = ['attempt_id', 'question_id', 'selected_options', 'is_correct', 'hint_used'];

    protected function casts(): array
    {
        return ['selected_options' => 'array', 'is_correct' => 'boolean', 'hint_used' => 'boolean'];
    }

    public function attempt() { return $this->belongsTo(TestAttempt::class, 'attempt_id'); }
    public function question() { return $this->belongsTo(TestQuestion::class, 'question_id'); }
}
