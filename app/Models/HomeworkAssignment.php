<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class HomeworkAssignment extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['course_id', 'title', 'description', 'difficulty', 'reward_coins', 'deadline', 'sort_order'];
    protected function casts(): array { return ['deadline' => 'date']; }

    public static function coinsByDifficulty(string $difficulty): int
    {
        return match($difficulty) {
            'easy' => 5, 'medium' => 15, 'hard' => 25, default => 15,
        };
    }

    public function course() { return $this->belongsTo(Course::class); }
    public function submissions() { return $this->hasMany(HomeworkSubmission::class, 'homework_id'); }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
