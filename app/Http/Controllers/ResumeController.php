<?php

namespace App\Http\Controllers;

use App\Models\{Resume, User};
use App\Services\WalletService;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function index()
    {
        $resumes = Resume::where('is_published', true)
            ->where('has_offer', false)
            ->with(['user' => fn($q) => $q->with(['certificates.course', 'media'])])
            ->paginate(12);

        return view('resume.index', compact('resumes'));
    }

    public function show(Resume $resume)
    {
        if (!$resume->is_published || $resume->has_offer) abort(404);
        $resume->load(['user.certificates.course', 'user.media']);
        return view('resume.show', compact('resume'));
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $resume = $user->resume ?? Resume::create(['user_id' => $user->id]);
        $courses = $user->enrollments()->with('certificates')->get();

        return view('resume.edit', compact('resume', 'courses'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'work_experience' => 'nullable|string',
            'project_links' => 'nullable|array',
            'project_links.*' => 'url',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'about' => 'nullable|string',
            'hidden_courses' => 'nullable|array',
            'hidden_courses.*' => 'integer',
            'has_offer' => 'boolean',
        ]);

        $user = $request->user();
        $resume = $user->resume ?? Resume::create(['user_id' => $user->id]);
        $resume->update($validated);

        return back()->with('success', 'Резюме оновлено.');
    }

    public function publish(Request $request)
    {
        $user = $request->user();

        // VIP includes free resume
        if ($user->isVip()) {
            $resume = $user->resume ?? Resume::create(['user_id' => $user->id]);
            $resume->update(['is_published' => true]);
            $user->update(['resume_published' => true, 'resume_expires_at' => $user->vip_expires_at]);
            return back()->with('success', 'Резюме опубліковано (VIP).');
        }

        // 100 coins for 1 year
        try {
            app(WalletService::class)->deduct($user, 100, 'resume_purchase', 'Розміщення резюме на рік');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        $resume = $user->resume ?? Resume::create(['user_id' => $user->id]);
        $resume->update(['is_published' => true]);
        $user->update(['resume_published' => true, 'resume_expires_at' => now()->addYear()]);

        return back()->with('success', 'Резюме опубліковано на 1 рік.');
    }
}
