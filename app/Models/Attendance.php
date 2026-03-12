<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['lesson_id', 'user_id', 'is_present', 'note'];
    protected function casts(): array { return ['is_present' => 'boolean']; }

    public function lesson() { return $this->belongsTo(Lesson::class); }
    public function user() { return $this->belongsTo(User::class); }
}
