<?php

namespace App\Http\Controllers;

use App\Models\{Test, TestQuestion, TestOption, TestAttempt, TestAttemptAnswer, Course};
use App\Services\{CoinRewardService, BonusService, WalletService};
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isTeacher() || $user->isAdmin()) {
            $tests = Test::whereHas('course', function ($q) use ($user) {
                if (!$user->isAdmin()) {
                    $q->where('teacher_id', $user->id);
                }
            })->with(['course', 'attempts'])->latest()->get();

            $courses = \App\Models\Course::when(!$user->isAdmin(), fn($q) => $q->where('teacher_id', $user->id))
                ->orderBy('title')->get();

            return view('test.index', compact('tests', 'courses'));
        }

        // Student: tests from enrolled courses
        $courseIds = $user->activeEnrollments()->pluck('courses.id');
        $tests = Test::whereIn('course_id', $courseIds)->with(['course'])->get();
        $attempts = $user->testAttempts()->whereIn('test_id', $tests->pluck('id'))->latest()->get()->groupBy('test_id');

        return view('test.index', compact('tests', 'attempts'));
    }

    // ── Teacher: manage tests ──────────────────────────────────

    private function authorizeTest(Test $test): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        $course = $test->course;
        if (!$course) abort(403);
        if ($user->isTeacher() && $course->teacher_id === $user->id) return;
        if ($user->isTeacher() && $course->coTeachers()->where('user_id', $user->id)->exists()) return;
        abort(403);
    }

    private function authorizeCourseForTest(Course $course): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        if ($user->isTeacher() && $course->teacher_id === $user->id) return;
        if ($user->isTeacher() && $course->coTeachers()->where('user_id', $user->id)->exists()) return;
        abort(403);
    }

    public function store(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        $this->authorizeCourseForTest($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:1|max:100',
        ]);

        $validated['course_id'] = $courseId;
        $test = Test::create($validated);

        return redirect()->route('teacher.tests.edit', $test)->with('success', 'Тест створено.');
    }

    public function edit(Test $test)
    {
        $this->authorizeTest($test);
        $test->load('questions.options');
        return view('test.edit', compact('test'));
    }

    public function update(Request $request, Test $test)
    {
        $this->authorizeTest($test);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:1|max:100',
        ]);
        $test->update($validated);
        return back()->with('success', 'Тест оновлено.');
    }

    /**
     * Add question to test
     */
    public function addQuestion(Request $request, Test $test)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|in:single,multiple',
            'hint' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question = $test->questions()->create([
            'text' => $validated['text'],
            'type' => $validated['type'],
            'hint' => $validated['hint'],
            'sort_order' => $test->questions()->count(),
        ]);

        foreach ($validated['options'] as $i => $opt) {
            $question->options()->create([
                'text' => $opt['text'],
                'is_correct' => $opt['is_correct'],
                'sort_order' => $i,
            ]);
        }

        return back()->with('success', 'Питання додано.');
    }

    public function updateQuestion(Request $request, TestQuestion $question)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|in:single,multiple',
            'hint' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*.id' => 'nullable|integer',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question->update([
            'text' => $validated['text'],
            'type' => $validated['type'],
            'hint' => $validated['hint'],
        ]);

        // Sync options
        $existingIds = [];
        foreach ($validated['options'] as $i => $opt) {
            if (!empty($opt['id'])) {
                TestOption::where('id', $opt['id'])->update([
                    'text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                    'sort_order' => $i,
                ]);
                $existingIds[] = $opt['id'];
            } else {
                $newOpt = $question->options()->create([
                    'text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                    'sort_order' => $i,
                ]);
                $existingIds[] = $newOpt->id;
            }
        }
        $question->options()->whereNotIn('id', $existingIds)->delete();

        return back()->with('success', 'Питання оновлено.');
    }

    public function deleteQuestion(TestQuestion $question)
    {
        $question->delete();
        return back()->with('success', 'Питання видалено.');
    }

    public function destroy(Test $test)
    {
        $user = auth()->user();

        // Teachers cannot directly delete — they must submit a deletion request
        if ($user->isTeacher()) {
            abort(403, 'Teachers must submit a deletion request.');
        }

        $courseId = $test->course_id;
        $test->questions()->each(fn($q) => $q->options()->delete() && $q->delete());
        $test->attempts()->delete();
        $test->delete();
        return redirect()->route('teacher.courses.edit', $courseId)->with('success', 'Тест видалено.');
    }

    // ── Student: take test ─────────────────────────────────────

    public function show(Test $test)
    {
        $user = auth()->user();

        // Check if already passed
        if ($test->userHasPassed($user->id)) {
            $lastAttempt = TestAttempt::where('test_id', $test->id)
                ->where('user_id', $user->id)
                ->where('passed', true)
                ->first();
            return view('test.result', compact('test', 'lastAttempt'));
        }

        $test->load('questions.options');
        $attemptCount = $test->userAttemptCount($user->id);

        return view('test.take', compact('test', 'attemptCount'));
    }

    /**
     * Start a test attempt
     */
    public function start(Request $request, Test $test)
    {
        $user = $request->user();

        if ($test->userHasPassed($user->id)) {
            return redirect()->route('tests.show', $test)->with('info', 'Ви вже склали цей тест.');
        }

        $attemptNumber = $test->userAttemptCount($user->id) + 1;

        // Charge for retake (attempt > 1)
        if ($attemptNumber > 1) {
            try {
                app(CoinRewardService::class)->testRetakeCharge($user, $test);
            } catch (\Exception $e) {
                return back()->with('error', 'Недостатньо монет для повторної спроби (-10).');
            }
        }

        $attempt = TestAttempt::create([
            'test_id' => $test->id,
            'user_id' => $user->id,
            'attempt_number' => $attemptNumber,
            'started_at' => now(),
        ]);

        $test->load('questions.options');
        return view('test.take', compact('test', 'attempt'));
    }

    /**
     * Submit test answers
     */
    public function submit(Request $request, TestAttempt $attempt)
    {
        $user = $request->user();
        if ($attempt->user_id !== $user->id) abort(403);
        if ($attempt->completed_at) {
            return redirect()->route('tests.show', $attempt->test_id);
        }

        $test = $attempt->test;
        $test->load('questions.options');
        $answers = $request->input('answers', []);
        $hintsUsed = $request->input('hints_used', []);

        $totalQuestions = $test->questions->count();
        $correctCount = 0;

        foreach ($test->questions as $question) {
            $selectedIds = (array) ($answers[$question->id] ?? []);
            $selectedIds = array_map('intval', $selectedIds);

            $correctIds = $question->correctOptions->pluck('id')->toArray();
            $isCorrect = !empty($selectedIds) && empty(array_diff($correctIds, $selectedIds)) && empty(array_diff($selectedIds, $correctIds));

            if ($isCorrect) $correctCount++;

            $hintUsed = in_array($question->id, $hintsUsed);

            TestAttemptAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_options' => $selectedIds,
                'is_correct' => $isCorrect,
                'hint_used' => $hintUsed,
            ]);
        }

        $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;
        $passed = $score >= $test->passing_score;

        $attempt->update([
            'score' => $score,
            'passed' => $passed,
            'hints_used' => count($hintsUsed),
            'completed_at' => now(),
        ]);

        // Award coins if passed
        if ($passed) {
            app(CoinRewardService::class)->testReward($user, $attempt);
        }

        return redirect()->route('tests.result', $attempt);
    }

    /**
     * Show test result
     */
    public function result(TestAttempt $attempt)
    {
        $user = auth()->user();
        if ($attempt->user_id !== $user->id && !$user->isTeacher() && !$user->isAdmin()) {
            abort(403);
        }

        $attempt->load(['answers.question', 'test.questions.options']);
        $test = $attempt->test;

        return view('test.result', compact('test', 'attempt'));
    }

    /**
     * Use a hint during test (AJAX)
     */
    public function useHint(Request $request, TestQuestion $question)
    {
        $user = $request->user();
        $test = $question->test;
        $courseId = $test->course_id;

        // Check max 5 hints per test attempt
        $currentAttempt = TestAttempt::where('test_id', $test->id)
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->first();

        if ($currentAttempt && $currentAttempt->hints_used >= 5) {
            return response()->json(['error' => 'Максимум 5 підказок на тест.'], 422);
        }

        $success = app(BonusService::class)->useTestHint($user, $courseId, $question->id);

        if (!$success) {
            return response()->json(['error' => 'Немає доступних підказок.'], 422);
        }

        return response()->json(['hint' => $question->hint]);
    }

    // ── Teacher: view statistics ───────────────────────────────

    public function statistics(Test $test)
    {
        $attempts = $test->attempts()
            ->with(['user', 'answers.question'])
            ->orderBy('user_id')
            ->orderBy('attempt_number')
            ->get();

        return view('test.statistics', compact('test', 'attempts'));
    }
}
