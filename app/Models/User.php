<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'first_name', 'last_name', 'phone', 'email', 'birthday', 'password',
        'google_id', 'role', 'is_vip', 'vip_expires_at', 'is_trusted_teacher',
        'login_streak', 'last_login_date', 'longest_streak', 'bio', 'avatar',
        'extra_avatars', 'resume_published', 'resume_expires_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'vip_expires_at' => 'datetime',
            'resume_expires_at' => 'datetime',
            'extra_avatars' => 'array',
            'is_vip' => 'boolean',
            'is_trusted_teacher' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
        $this->addMediaCollection('extra_avatars')->onlyKeepLatest(5);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Role checks
    public function isSuperAdmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin(): bool { return in_array($this->role, ['superadmin', 'admin']); }
    public function isTeacher(): bool { return $this->role === 'teacher'; }
    public function isStudent(): bool { return $this->role === 'student'; }
    public function isParent(): bool { return $this->role === 'parent'; }
    public function isRegistered(): bool { return $this->role === 'registered'; }

    public function hasRole(string|array $roles): bool
    {
        return in_array($this->role, (array) $roles);
    }

    public function isActiveStudent(): bool
    {
        return $this->enrollments()
            ->where('status', 'active')
            ->where('active_until', '>', now())
            ->exists();
    }

    public function isVip(): bool
    {
        return $this->is_vip && $this->vip_expires_at && $this->vip_expires_at->isFuture();
    }

    // Relationships
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet ?? Wallet::create(['user_id' => $this->id, 'balance' => 0]);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function taughtCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function coTeacherCourses()
    {
        return $this->belongsToMany(Course::class, 'course_co_teachers')->withTimestamps();
    }

    public function enrollments()
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withPivot(['status', 'is_paid', 'paid_amount', 'discount_percent', 'enrolled_at', 'completed_at', 'active_until', 'success_rate', 'review_submitted', 'telegram_link_shown'])
            ->withTimestamps();
    }

    public function activeEnrollments()
    {
        return $this->enrollments()->wherePivot('status', 'active');
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'parent_child', 'parent_id', 'child_id')
            ->withTimestamps();
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_child', 'child_id', 'parent_id')
            ->withTimestamps();
    }

    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function resume()
    {
        return $this->hasOne(Resume::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot(['coins_awarded', 'earned_at'])
            ->withTimestamps();
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function receivedNotes()
    {
        return $this->hasMany(Note::class, 'recipient_id');
    }

    public function notifications()
    {
        return $this->hasMany(PlatformNotification::class);
    }

    public function pushSubscriptions()
    {
        return $this->hasMany(PushSubscription::class);
    }

    public function bonusInventory()
    {
        return $this->hasMany(BonusInventory::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function totalDonated(): int
    {
        return $this->donations()->sum('amount');
    }
}
