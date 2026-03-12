<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['location_id', 'name', 'capacity', 'is_active'];

    public function location() { return $this->belongsTo(Location::class); }
    public function lessons() { return $this->hasMany(Lesson::class); }
}
