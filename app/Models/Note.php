<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['user_id', 'recipient_id', 'course_id', 'content', 'is_read'];
    protected function casts(): array { return ['is_read' => 'boolean']; }

    public function author() { return $this->belongsTo(User::class, 'user_id'); }
    public function recipient() { return $this->belongsTo(User::class, 'recipient_id'); }
    public function course() { return $this->belongsTo(Course::class); }

    public function scopePersonal($q, int $userId) { return $q->where('user_id', $userId)->whereNull('recipient_id'); }
    public function scopeReceivedBy($q, int $userId) { return $q->where('recipient_id', $userId); }
}
