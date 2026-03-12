<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GraduationSubmission extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'project_id', 'user_id', 'links', 'status', 'teacher_comment',
        'revision_count', 'coins_awarded', 'is_defended', 'freeze_days_used',
        'effective_deadline', 'reviewed_by', 'submitted_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'is_defended' => 'boolean',
            'effective_deadline' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')->useDisk('public');
    }

    public function project() { return $this->belongsTo(GraduationProject::class, 'project_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function calculateReward(): int
    {
        return max(25, 100 - ($this->revision_count * 5));
    }
}
