<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GraduationProject extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'deadline'];
    protected function casts(): array { return ['deadline' => 'date']; }

    public function course() { return $this->belongsTo(Course::class); }
    public function submissions() { return $this->hasMany(GraduationSubmission::class, 'project_id'); }
}
