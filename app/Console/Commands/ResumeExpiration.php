<?php

namespace App\Console\Commands;

use App\Models\{User, Resume};
use Illuminate\Console\Command;

/**
 * Expire resume placements.
 * Run daily: 0 0 * * * php artisan resumes:expire
 */
class ResumeExpiration extends Command
{
    protected $signature = 'resumes:expire';
    protected $description = 'Expire resume placements past their date';

    public function handle(): void
    {
        $users = User::where('resume_published', true)
            ->where('resume_expires_at', '<', now())
            ->get();

        foreach ($users as $user) {
            $user->update(['resume_published' => false]);
            if ($user->resume) {
                $user->resume->update(['is_published' => false]);
            }
            $this->info("Resume expired: {$user->full_name}");
        }
    }
}
