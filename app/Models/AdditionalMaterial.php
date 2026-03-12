<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdditionalMaterial extends Model
{
    protected $fillable = ['course_id', 'teacher_id', 'title', 'description', 'url', 'price_coins'];

    public function course() { return $this->belongsTo(Course::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function purchases() { return $this->hasMany(MaterialPurchase::class, 'material_id'); }
}
