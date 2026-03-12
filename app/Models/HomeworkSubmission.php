<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HomeworkSubmission extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'homework_id', 'user_id', 'links', 'status', 'teacher_comment',
        'revision_count', 'early_submission', 'coins_awarded', 'freeze_days_used',
        'effective_deadline', 'reviewed_by', 'submitted_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'links' => 'array',
            'early_submission' => 'boolean',
            'coins_awarded' => 'boolean',
            'effective_deadline' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('files')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useDisk('public');
    }

    public function homework() { return $this->belongsTo(HomeworkAssignment::class, 'homework_id'); }
    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
