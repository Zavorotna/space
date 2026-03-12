<?php

namespace App\Http\Controllers;

use App\Models\{GraduationProject, GraduationSubmission, Course};
use App\Services\{CoinRewardService, BonusService, WalletService};
use Illuminate\Http\Request;

class GraduationProjectController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date|after:today',
        ]);
        $validated['course_id'] = $course->id;
        GraduationProject::create($validated);
        return back()->with('success', 'Випускний проєкт створено.');
    }

    public function update(Request $request, GraduationProject $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'deadline' => 'required|date',
        ]);
        $project->update($validated);
        return back()->with('success', 'Проєкт оновлено.');
    }

    public function submit(Request $request, GraduationProject $project)
    {
        $user = $request->user();
        $validated = $request->validate([
            'files.*' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'links' => 'nullable|string',
        ]);

        $submission = GraduationSubmission::firstOrNew([
            'project_id' => $project->id,
            'user_id' => $user->id,
        ]);

        $links = array_filter(array_map('trim', explode("\n", $validated['links'] ?? '')));

        $submission->fill([
            'links' => $links,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $submission->save();

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $submission->addMedia($file)->toMediaCollection('files');
            }
        }

        return back()->with('success', 'Випускний проєкт відправлено.');
    }

    public function review(Request $request, GraduationSubmission $submission)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,revision,commission',
            'teacher_comment' => 'nullable|string',
        ]);

        $submission->update([
            'status' => $validated['status'],
            'teacher_comment' => $validated['teacher_comment'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($validated['status'] === 'revision') {
            $submission->increment('revision_count');
            try {
                app(WalletService::class)->deduct($submission->user, 5, 'penalty', 'Випускний на доопрацювання: -5');
            } catch (\Exception $e) {}
        }

        if ($validated['status'] === 'accepted') {
            $submission->update(['is_defended' => true]);
            app(CoinRewardService::class)->graduationReward($submission->user, $submission);
        }

        return back()->with('success', 'Оцінку проєкту виставлено.');
    }

    public function freezeDeadline(Request $request, GraduationSubmission $submission)
    {
        $request->validate(['days' => 'required|integer|min:1|max:20']);
        try {
            $result = app(BonusService::class)->freezeGraduationDeadline(
                $request->user(), $submission, $request->days
            );
            if (!$result) return back()->with('error', 'Недостатньо заморозок або ліміт (макс 20 днів).');
            return back()->with('success', "Дедлайн заморожено на {$request->days} дн.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submissions(GraduationProject $project)
    {
        $submissions = $project->submissions()->with(['user', 'media'])->get();
        return view('project.submissions', compact('project', 'submissions'));
    }
}
