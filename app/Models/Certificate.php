<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Certificate extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'course_id', 'user_id', 'type', 'success_rate',
        'discount_next_course', 'discount_used', 'certificate_number',
    ];

    protected function casts(): array
    {
        return ['discount_used' => 'boolean', 'success_rate' => 'decimal:2'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('certificate_image')->singleFile();
    }

    public function course() { return $this->belongsTo(Course::class); }
    public function user() { return $this->belongsTo(User::class); }

    public static function determineType(float $successRate, bool $defended): string
    {
        if ($successRate >= 75 && $defended) return 'guaranteed';
        if ($successRate >= 50) return 'color';
        return 'bw';
    }

    public static function discountForType(string $type): int
    {
        return match($type) {
            'guaranteed' => 20, 'color' => 10, default => 0,
        };
    }
}
