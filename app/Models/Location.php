<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'address', 'city', 'street', 'work_start', 'work_end', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }

    public function classrooms() { return $this->hasMany(Classroom::class); }
    public function lessons() { return $this->hasMany(Lesson::class); }
}
