<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseApplication extends Model
{
    protected $fillable = ['course_id', 'user_id', 'status', 'note', 'processed_by'];

    public function course() { return $this->belongsTo(Course::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }
}
