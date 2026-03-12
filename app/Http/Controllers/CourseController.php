<?php

namespace App\Http\Controllers;

use App\Models\{Course, CourseApplication, CourseReview, User};
use App\Services\{WalletService, CoinRewardService, LiqPayService};
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // ── Public pages ───────────────────────────────────────────

    /**
     * Public course listing (group courses only, published)
     */
    public function publicIndex()
    {
        $courses = Course::published()->group()
            ->with(['teacher', 'media'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(12);

        return view('public.courses', compact('courses'));
    }

    /**
     * Public course detail page
     */
    public function publicShow(Course $course)
    {
        if (!$course->is_published || ($course->type === 'individual' && !auth()->check())) {
            abort(404);
        }

        $course->load(['teacher', 'reviews' => fn($q) => $q->where('is_approved', true)->with('user'), 'media']);

        return view('public.course-detail', compact('course'));
    }

    // ── Student actions ────────────────────────────────────────

    /**
     * Apply for a course
     */
    public function apply(Request $request, Course $course)
    {
        $user = $request->user();

        if ($course->students()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Ви вже записані на цей курс.');
        }

        if (CourseApplication::where('course_id', $course->id)->where('user_id', $user->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Ваша заявка вже розглядається.');
        }

        CourseApplication::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'note' => $request->input('note'),
        ]);

        return back()->with('success', 'Заявку подано. Очікуйте підтвердження.');
    }

    /**
     * Student course page (enrolled)
     */
    public function studentShow(Request $request, Course $course)
    {
        $user = $request->user();
        $enrollment = $course->students()->where('user_id', $user->id)->first();

        if (!$enrollment || !in_array($enrollment->pivot->status, ['active', 'completed'])) {
            abort(403);
        }

        $course->load([
            'homeworkAssignments' => fn($q) => $q->orderBy('sort_order'),
            'tests' => fn($q) => $q->orderBy('sort_order'),
            'graduationProject',
            'additionalMaterials',
            'teacher',
        ]);

        $homeworkSubmissions = $user->homeworkSubmissions()
            ->whereIn('homework_id', $course->homeworkAssignments->pluck('id'))
            ->get()
            ->keyBy('homework_id');

        $testAttempts = $user->testAttempts()
            ->whereIn('test_id', $course->tests->pluck('id'))
            ->get()
            ->groupBy('test_id');

        // Show telegram link if paid
        $showTelegram = $enrollment->pivot->is_paid && $course->telegram_link;

        return view('student.course', compact(
            'course', 'enrollment', 'homeworkSubmissions', 'testAttempts', 'showTelegram'
        ));
    }

    /**
     * Submit course review
     */
    public function submitReview(Request $request, Course $course)
    {
        $user = $request->user();
        $enrollment = $course->students()->where('user_id', $user->id)->first();

        if (!$enrollment || $enrollment->pivot->review_submitted) {
            return back()->with('error', 'Відгук вже надано або ви не на курсі.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'nullable|string|max:2000',
        ]);

        $review = CourseReview::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'text' => $validated['text'],
        ]);

        $course->students()->updateExistingPivot($user->id, ['review_submitted' => true]);

        // Reward 100 coins
        app(CoinRewardService::class)->reviewReward($user, $review);

        return back()->with('success', 'Дякуємо за відгук! Нараховано 100 монет.');
    }

    // ── Teacher/Admin actions ──────────────────────────────────

    /**
     * Teacher: create course form
     */
    public function create()
    {
        return view('teacher.course-create');
    }

    /**
     * Teacher: store new course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:one_time,monthly',
            'type' => 'required|in:group,individual',
            'intro_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'telegram_link' => 'nullable|url',
            'has_graduation_project' => 'boolean',
            'cover' => 'nullable|image|max:5120',
        ]);

        $validated['teacher_id'] = $request->user()->isTeacher()
            ? $request->user()->id
            : $request->input('teacher_id', $request->user()->id);

        $course = Course::create($validated);

        if ($request->hasFile('cover')) {
            $course->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return redirect()->route('courses.edit', $course)->with('success', 'Курс створено.');
    }

    /**
     * Edit course
     */
    public function edit(Course $course)
    {
        $this->authorizeCourse($course);
        $course->load(['homeworkAssignments', 'tests.questions.options', 'graduationProject', 'additionalMaterials']);
        return view('teacher.course-edit', compact('course'));
    }

    /**
     * Update course
     */
    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'program' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:one_time,monthly',
            'status' => 'required|in:waiting,enrolling,active,completed',
            'type' => 'required|in:group,individual',
            'intro_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'telegram_link' => 'nullable|url',
            'has_graduation_project' => 'boolean',
            'is_published' => 'boolean',
            'cover' => 'nullable|image|max:5120',
        ]);

        $course->update($validated);

        if ($request->hasFile('cover')) {
            $course->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return back()->with('success', 'Курс оновлено.');
    }

    /**
     * Duplicate course from template
     */
    public function duplicate(Course $course)
    {
        $this->authorizeCourse($course);
        $newCourse = $course->duplicateAsTemplate();
        return redirect()->route('courses.edit', $newCourse)->with('success', 'Курс скопійовано.');
    }

    /**
     * Manage applications
     */
    public function applications(Course $course)
    {
        $this->authorizeCourse($course);
        $applications = $course->applications()->with('user')->where('status', 'pending')->get();
        return view('teacher.course-applications', compact('course', 'applications'));
    }

    /**
     * Approve application
     */
    public function approveApplication(Request $request, CourseApplication $application)
    {
        $course = $application->course;
        $this->authorizeCourse($course);

        $application->update([
            'status' => 'approved',
            'processed_by' => $request->user()->id,
        ]);

        // Add student to course (pending payment)
        $course->students()->attach($application->user_id, [
            'status' => 'approved',
        ]);

        // Change user role to student if registered
        $student = $application->user;
        if ($student->role === 'registered') {
            $student->update(['role' => 'student']);
        }

        return back()->with('success', 'Заявку підтверджено.');
    }

    /**
     * Teacher adds student directly (without application)
     */
    public function addStudent(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($course->students()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'Студент вже на курсі.');
        }

        $course->students()->attach($validated['user_id'], [
            'status' => 'active',
            'enrolled_at' => now(),
            'active_until' => now()->addYear(),
        ]);

        $student = User::find($validated['user_id']);
        if ($student && $student->role === 'registered') {
            $student->update(['role' => 'student']);
        }

        return back()->with('success', 'Студента додано.');
    }

    /**
     * Course students list with progress
     */
    public function students(Course $course)
    {
        $this->authorizeCourse($course);
        $students = $course->students()->with('wallet')->get();
        return view('teacher.course-students', compact('course', 'students'));
    }

    /**
     * Update end date
     */
    public function updateEndDate(Request $request, Course $course)
    {
        $this->authorizeCourse($course);
        $request->validate(['end_date' => 'required|date']);
        $course->update(['end_date' => $request->end_date]);
        return back()->with('success', 'Дату завершення оновлено.');
    }

    // ── LiqPay payment for course ──────────────────────────────

    public function payForm(Course $course)
    {
        $user = auth()->user();
        $enrollment = $course->students()->where('user_id', $user->id)->first();

        if (!$enrollment || $enrollment->pivot->is_paid) {
            return redirect()->route('courses.student.show', $course);
        }

        // Check for certificate discounts
        $discount = 0;
        $unusedCert = $user->certificates()
            ->where('discount_next_course', '>', 0)
            ->where('discount_used', false)
            ->orderByDesc('discount_next_course')
            ->first();

        if ($unusedCert) {
            $discount = $unusedCert->discount_next_course;
        }

        // VIP discount
        if ($user->isVip()) {
            $discount = max($discount, 5);
        }

        $finalPrice = (int) round($course->price * (1 - $discount / 100));

        $liqpay = LiqPayService::forCourse($course);
        $orderId = 'course_' . $course->id . '_user_' . $user->id . '_' . time();

        $paymentData = $liqpay->createPayment(
            $finalPrice,
            $orderId,
            "Оплата курсу: {$course->title}",
            route('courses.pay.result', $course),
            route('liqpay.callback')
        );

        return view('student.course-pay', compact('course', 'paymentData', 'discount', 'finalPrice'));
    }

    /**
     * Authorization helper
     */
    protected function authorizeCourse(Course $course): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin()) return;
        if ($user->isTeacher() && $course->teacher_id === $user->id) return;
        abort(403);
    }

    /**
     * Process course payment (coins)
     */
    public function payProcess(Request $request, Course $course)
    {
        $user = $request->user();
        $enrollment = $course->students()->where('user_id', $user->id)->first();
        if (!$enrollment || $enrollment->pivot->is_paid) {
            return redirect()->route('courses.student.show', $course);
        }

        $discount = 0;
        $unusedCert = $user->certificates()
            ->where('discount_next_course', '>', 0)
            ->where('discount_used', false)
            ->orderByDesc('discount_next_course')
            ->first();
        if ($unusedCert) $discount = $unusedCert->discount_next_course;
        if ($user->isVip()) $discount = max($discount, 5);

        $finalPrice = (int) round($course->price * (1 - $discount / 100));

        try {
            app(\App\Services\WalletService::class)->payCourse($user, $course, $finalPrice);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        // Mark certificate discount as used
        if ($unusedCert) $unusedCert->update(['discount_used' => true]);

        // Mark enrollment as paid
        $course->students()->updateExistingPivot($user->id, [
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        // Set active_until
        $user->update(['active_until' => now()->addYear()]);

        return redirect()->route('courses.student.show', $course)->with('success', 'Курс оплачено!');
    }

    /**
     * Payment result page (LiqPay redirect back)
     */
    public function payResult(Course $course)
    {
        return redirect()->route('courses.student.show', $course)
            ->with('info', 'Очікуємо підтвердження оплати від банку.');
    }
}
