<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformNotification extends Model
{
    protected $fillable = ['user_id', 'type', 'title', 'message', 'link', 'is_read', 'push_sent', 'deletion_request_id'];
    protected function casts(): array { return ['user_id' => 'integer', 'deletion_request_id' => 'integer', 'is_read' => 'boolean', 'push_sent' => 'boolean']; }

    public function user() { return $this->belongsTo(User::class); }
    public function deletionRequest() { return $this->belongsTo(DeletionRequest::class); }
    public function scopeUnread($q) { return $q->where('is_read', false); }
}
