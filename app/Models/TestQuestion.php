<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    protected $fillable = ['test_id', 'text', 'type', 'hint', 'sort_order'];

    public function test() { return $this->belongsTo(Test::class); }
    public function options() { return $this->hasMany(TestOption::class, 'question_id')->orderBy('sort_order'); }
    public function correctOptions() { return $this->options()->where('is_correct', true); }
}
