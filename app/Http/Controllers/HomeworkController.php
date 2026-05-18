<?php

namespace App\Http\Controllers;

use App\Models\{HomeworkAssignment, HomeworkSubmission, Course};
use App\Services\{CoinRewardService, BonusService, WalletService};
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeworkController extends Controller
{
    // ── Teacher: manage homework ───────────────────────────────

    public function store(Request $request, Course $course)
    {
        $this->authorizeCourseTeacher($course);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'deadline' => 'required|date|after:today',
        ]);

        $validated['course_id'] = $course->id;
        $validated['reward_coins'] = HomeworkAssignment::coinsByDifficulty($validated['difficulty']);

        $hw = HomeworkAssignment::create($validated);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $hw->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return back()->with('success', 'Домашнє завдання створено.');
    }

    public function update(Request $request, HomeworkAssignment $homework)
    {
        $this->authorizeCourseTeacher($homework->course);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'deadline' => 'required|date',
        ]);

        $validated['reward_coins'] = HomeworkAssignment::coinsByDifficulty($validated['difficulty']);
        $homework->update($validated);

        return back()->with('success', 'Завдання оновлено.');
    }

    public function destroy(HomeworkAssignment $homework)
    {
        $this->authorizeCourseTeacher($homework->course);
        $homework->delete();
        return back()->with('success', 'Завдання видалено.');
    }

    private function authorizeCourseTeacher(\App\Models\Course $course): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        if ($user->isTeacher() && $course->teacher_id === $user->id) return;
        if ($user->isTeacher() && $course->coTeachers()->where('user_id', $user->id)->exists()) return;
        abort(403);
    }

    // ── Student: submit homework ───────────────────────────────

    public function showSubmitForm(HomeworkAssignment $homework)
    {
        $user = auth()->user();
        $submission = $homework->submissions()->where('user_id', $user->id)->first();
        return view('homework.submit', compact('homework', 'submission'));
    }

    public function submit(Request $request, HomeworkAssignment $homework)
    {
        $user = $request->user();

        $validated = $request->validate([
            'files.*' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'links' => 'nullable|string',
            'link_url' => 'nullable|url',
        ]);

        $submission = HomeworkSubmission::firstOrNew([
            'homework_id' => $homework->id,
            'user_id' => $user->id,
        ]);

        $links = array_filter(array_map('trim', explode("\n", $validated['links'] ?? '')));
        if (!empty($validated['link_url'])) $links[] = $validated['link_url'];

        $effectiveDeadline = $submission->effective_deadline ?? $homework->deadline;
        $isEarly = Carbon::today()->diffInDays($effectiveDeadline, false) >= 2;

        $submission->fill([
            'links' => $links,
            'status' => 'submitted',
            'early_submission' => $isEarly && $submission->revision_count === 0,
            'submitted_at' => now(),
        ]);
        $submission->save();

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $submission->addMedia($file)->toMediaCollection('files');
            }
        }

        return redirect()->route('courses.student.show', $homework->course_id)
            ->with('success', 'Домашку відправлено.');
    }

    // ── Teacher: review homework ───────────────────────────────

    public function submissions(HomeworkAssignment $homework)
    {
        $submissions = $homework->submissions()
            ->with(['user', 'media'])
            ->latest('submitted_at')
            ->get();

        return view('homework.submissions', compact('homework', 'submissions'));
    }

    public function review(Request $request, HomeworkSubmission $submission)
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,revision',
            'teacher_comment' => 'nullable|string|max:2000',
        ]);

        $submission->update([
            'status' => $validated['status'],
            'teacher_comment' => $validated['teacher_comment'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($validated['status'] === 'revision') {
            $submission->increment('revision_count');
            // -1 coin per revision
            try {
                app(WalletService::class)->deduct(
                    $submission->user, 1, 'penalty',
                    "Домашка на доопрацювання: -1",
                    HomeworkSubmission::class, $submission->id
                );
            } catch (\Exception $e) {
                // Student may not have coins — that's OK
            }
        }

        if ($validated['status'] === 'accepted' && !$submission->coins_awarded) {
            app(CoinRewardService::class)->homeworkReward($submission->user, $submission);
        }

        return back()->with('success', 'Оцінку виставлено.');
    }

    // ── Student: apply deadline freeze ─────────────────────────

    public function freezeDeadline(Request $request, HomeworkSubmission $submission)
    {
        $request->validate(['days' => 'required|integer|min:1|max:5']);

        try {
            $result = app(BonusService::class)->freezeHomeworkDeadline(
                $request->user(), $submission, $request->days
            );

            if (!$result) {
                return back()->with('error', 'Недостатньо заморозок або досягнуто ліміт (макс 5 днів).');
            }

            return back()->with('success', "Дедлайн заморожено на {$request->days} дн.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
