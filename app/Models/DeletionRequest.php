<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphTo};

class DeletionRequest extends Model
{
    protected $fillable = ['requester_id', 'reason', 'deletable_type', 'deletable_id', 'status', 'processed_by'];

    public function deletable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PlatformNotification::class);
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }
}