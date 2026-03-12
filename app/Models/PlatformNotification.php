<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'link', 'is_read', 'push_sent'];
    protected function casts(): array { return ['is_read' => 'boolean', 'push_sent' => 'boolean']; }

    public function user() { return $this->belongsTo(User::class); }
    public function scopeUnread($q) { return $q->where('is_read', false); }
}
