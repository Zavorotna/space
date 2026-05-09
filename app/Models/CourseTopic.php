<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTopic extends Model
{
    protected $fillable = ['course_id', 'title', 'sort_order'];

    public function course() { return $this->belongsTo(Course::class); }
    public function tests()  { return $this->hasMany(Test::class, 'activation_topic_id'); }
}