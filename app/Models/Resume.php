<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    protected $fillable = [
        'user_id', 'work_experience', 'project_links', 'contact_email',
        'contact_phone', 'about', 'hidden_courses', 'is_published', 'has_offer',
    ];

    protected function casts(): array
    {
        return [
            'project_links' => 'array', 'hidden_courses' => 'array',
            'is_published' => 'boolean', 'has_offer' => 'boolean',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }

    public function visibleCertificates()
    {
        $hidden = $this->hidden_courses ?? [];
        return $this->user->certificates()->whereNotIn('course_id', $hidden)->get();
    }
}
