<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestOption extends Model
{
    protected $fillable = ['question_id', 'text', 'is_correct', 'sort_order'];
    protected function casts(): array { return ['is_correct' => 'boolean']; }

    public function question() { return $this->belongsTo(TestQuestion::class, 'question_id'); }
}
