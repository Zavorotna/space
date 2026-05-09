<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Course extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title', 'description', 'program', 'teacher_id', 'price', 'billing_period',
        'status', 'type', 'intro_date', 'start_date', 'end_date', 'telegram_link',
        'liqpay_merchant_id', 'liqpay_private_key', 'has_graduation_project',
        'template_id', 'is_published', 'is_template',
        'schedule_days', 'schedule_times', 'schedule_start_time', 'schedule_end_time',
        'schedule_mode', 'schedule_location_id', 'schedule_classroom_id',
    ];

    protected function casts(): array
    {
        return [
            'intro_date'             => 'date',
            'start_date'             => 'date',
            'end_date'               => 'date',
            'price'                  => 'decimal:2',
            'is_published'           => 'boolean',
            'is_template'            => 'boolean',
            'has_graduation_project' => 'boolean',
            'teacher_id'             => 'integer',
            'schedule_days'          => 'array',
            'schedule_times'         => 'array',
            'schedule_location_id'   => 'integer',
            'schedule_classroom_id'  => 'integer',
        ];
    }

    public function hasSchedule(): bool
    {
        if (!$this->start_date || !$this->end_date || empty($this->schedule_days)) return false;
        if (!empty($this->schedule_times)) return true;
        return $this->schedule_start_time && $this->schedule_end_time;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function topics()
    {
        return $this->hasMany(CourseTopic::class)->orderBy('sort_order');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot(['status', 'is_paid', 'paid_amount', 'discount_percent', 'enrolled_at', 'completed_at', 'active_until', 'success_rate', 'review_submitted', 'telegram_link_shown'])
            ->withTimestamps();
    }

    public function activeStudents()
    {
        return $this->students()->wherePivot('status', 'active');
    }

    public function applications()
    {
        return $this->hasMany(CourseApplication::class);
    }

    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function homeworkAssignments()
    {
        return $this->hasMany(HomeworkAssignment::class);
    }

    public function tests()
    {
        return $this->hasMany(Test::class);
    }

    public function graduationProject()
    {
        return $this->hasOne(GraduationProject::class);
    }

    public function additionalMaterials()
    {
        return $this->hasMany(AdditionalMaterial::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function coTeachers()
    {
        return $this->belongsToMany(User::class, 'course_co_teachers')->withTimestamps();
    }

    public function template()
    {
        return $this->belongsTo(Course::class, 'template_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function duplicateAsTemplate(): self
    {
        $new = $this->replicate(['status', 'start_date', 'end_date']);
        $new->template_id = $this->id;
        $new->status = 'waiting';
        $new->is_published = false;
        $new->is_template = false;
        $new->save();

        // Copy tests
        foreach ($this->tests as $test) {
            $newTest = $test->replicate();
            $newTest->course_id = $new->id;
            $newTest->save();
            foreach ($test->questions as $q) {
                $newQ = $q->replicate();
                $newQ->test_id = $newTest->id;
                $newQ->save();
                foreach ($q->options as $o) {
                    $newO = $o->replicate();
                    $newO->question_id = $newQ->id;
                    $newO->save();
                }
            }
        }

        // Copy homework assignments
        foreach ($this->homeworkAssignments as $hw) {
            $newHw = $hw->replicate(['deadline']);
            $newHw->course_id = $new->id;
            $newHw->save();
        }

        // Copy graduation project
        if ($this->graduationProject) {
            $newGp = $this->graduationProject->replicate(['deadline']);
            $newGp->course_id = $new->id;
            $newGp->save();
        }

        return $new;
    }
}
