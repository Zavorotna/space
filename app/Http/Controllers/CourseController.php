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
        if (auth()->check() && (auth()->user()->isTeacher() || auth()->user()->isAdmin())) {
            return redirect()->route('teacher.courses.index');
        }

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
     * Teacher/Admin: courses & templates list
     */
    public function teacherCourses()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $courses = Course::where('is_template', false)->with('teacher')->latest()->get();
            $templates = Course::where('is_template', true)->with('teacher')->latest()->get();
        } else {
            // Hide courses/templates that this teacher has submitted a pending deletion request for
            $pendingIds = \App\Models\DeletionRequest::where('requester_id', $user->id)
                ->where('deletable_type', Course::class)
                ->pending()
                ->pluck('deletable_id');

            $coTeachingIds = $user->coTeacherCourses()->pluck('courses.id');
            $courses = Course::where('is_template', false)
                ->where(fn($q) => $q->where('teacher_id', $user->id)->orWhereIn('id', $coTeachingIds))
                ->whereNotIn('id', $pendingIds)
                ->with('teacher')->latest()->get();
            $templates = Course::where('is_template', true)
                ->where(fn($q) => $q->where('teacher_id', $user->id)->orWhereNull('teacher_id'))
                ->whereNotIn('id', $pendingIds)
                ->latest()->get();
        }

        return view('teacher.courses', compact('courses', 'templates'));
    }

    /**
     * Teacher: create course form
     */
    public function create()
    {
        $locations = \App\Models\Location::where('is_active', true)->with('classrooms')->get();
        return view('teacher.course-create', compact('locations'));
    }

    /**
     * Teacher: store new course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'program'                => 'nullable|string',
            'price'                  => 'required|numeric|min:0',
            'billing_period'         => 'required|in:one_time,monthly,per_lesson',
            'type'                   => 'required|in:group,individual',
            'intro_date'             => 'nullable|date',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'telegram_link'          => 'nullable|url',
            'has_graduation_project' => 'boolean',
            'is_template'            => 'boolean',
            'cover'                  => 'nullable|image|max:5120',
            'schedule_days'          => 'nullable|array',
            'schedule_days.*'        => 'integer|between:1,7',
            'schedule_start_time'    => 'nullable|date_format:H:i',
            'schedule_end_time'      => 'nullable|date_format:H:i|after:schedule_start_time',
            'schedule_mode'          => 'nullable|in:online,offline',
            'schedule_location_id'   => 'nullable|exists:locations,id',
            'schedule_classroom_id'  => 'nullable|exists:classrooms,id',
        ]);

        $validated['teacher_id'] = $request->user()->isTeacher()
            ? $request->user()->id
            : $request->input('teacher_id', $request->user()->id);

        $course = Course::create(collect($validated)->except('cover')->toArray());

        if ($request->hasFile('cover')) {
            $course->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        // Auto-generate lessons for the primary teacher
        $generated = app(\App\Services\ScheduleService::class)
            ->generateCourseLessons($course, \App\Models\User::find($course->teacher_id));

        $msg = 'Курс створено.';
        if ($generated > 0) $msg .= " Автоматично додано {$generated} занять до розкладу.";

        return redirect()->route('teacher.courses.edit', $course)->with('success', $msg);
    }

    /**
     * Edit course
     */
    public function edit(Course $course)
    {
        $this->authorizeCourse($course);
        $course->load(['homeworkAssignments', 'tests.questions.options', 'graduationProject', 'additionalMaterials', 'coTeachers',
            'students' => fn($q) => $q->withPivot(['status', 'is_paid', 'enrolled_at', 'active_until'])]);
        $teachers  = \App\Models\User::whereIn('role', ['teacher', 'admin', 'superadmin'])->orderBy('last_name')->get();
        $locations = \App\Models\Location::where('is_active', true)->with('classrooms')->get();
        return view('teacher.course-edit', compact('course', 'teachers', 'locations'));
    }

    /**
     * Update course
     */
    public function update(Request $request, Course $course)
    {
        $this->authorizeCourse($course);

        $rules = [
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'program'                => 'nullable|string',
            'price'                  => 'required|numeric|min:0',
            'billing_period'         => 'required|in:one_time,monthly,per_lesson',
            'status'                 => 'required|in:waiting,enrolling,active,completed',
            'type'                   => 'required|in:group,individual',
            'intro_date'             => 'nullable|date',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date',
            'telegram_link'          => 'nullable|url',
            'has_graduation_project' => 'boolean',
            'is_published'           => 'boolean',
            'cover'                  => 'nullable|image|max:5120',
            'schedule_days'          => 'nullable|array',
            'schedule_days.*'        => 'integer|between:1,7',
            'schedule_start_time'    => 'nullable|date_format:H:i',
            'schedule_end_time'      => 'nullable|date_format:H:i',
            'schedule_mode'          => 'nullable|in:online,offline',
            'schedule_location_id'   => 'nullable|exists:locations,id',
            'schedule_classroom_id'  => 'nullable|exists:classrooms,id',
        ];

        if ($request->user()->isAdmin()) {
            $rules['teacher_id'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        // Ensure schedule_days is explicitly set (absent when no checkboxes checked)
        if (!array_key_exists('schedule_days', $validated)) {
            $validated['schedule_days'] = null;
        }
        // Guard: schedule_mode must never be null (NOT NULL column)
        if (empty($validated['schedule_mode'])) {
            $validated['schedule_mode'] = 'online';
        }
        // Guard: never null out teacher_id (NOT NULL column) — admin left the empty option selected
        if (array_key_exists('teacher_id', $validated) && empty($validated['teacher_id'])) {
            unset($validated['teacher_id']);
        }

        $scheduleService = app(\App\Services\ScheduleService::class);
        $generatedMsg = '';

        $scheduleChanged = !$course->is_template && $this->scheduleChanged($course, $validated);
        $oldTeacherId = $course->teacher_id;

        $course->update(collect($validated)->except('cover')->toArray());
        $course->refresh();

        $newTeacherId = $course->teacher_id;

        // Notify if teacher reassigned
        if ($request->user()->isAdmin() && $newTeacherId && $newTeacherId != $oldTeacherId) {
            $teacher = \App\Models\User::find($newTeacherId);
            if ($teacher) {
                app(\App\Services\NotificationService::class)->notify(
                    $teacher,
                    'course_assigned',
                    'Вас призначено викладачем курсу',
                    "Курс: {$course->title}",
                    route('teacher.courses.edit', $course)
                );
            }
        }

        if ($scheduleChanged && $course->hasSchedule()) {
            // Delete all unstarted lessons and regenerate for all teachers
            \App\Models\Lesson::where('course_id', $course->id)
                ->whereNull('completion_status')
                ->delete();
            $total = 0;
            if ($course->teacher_id) {
                $total += $scheduleService->generateCourseLessons($course, \App\Models\User::find($course->teacher_id));
            }
            foreach ($course->coTeachers as $coTeacher) {
                $total += $scheduleService->generateCourseLessons($course, $coTeacher);
            }
            $generatedMsg = $total > 0
                ? " Розклад змінено, перегенеровано {$total} занять."
                : " Розклад змінено, незаплановані заняття видалено.";
        } elseif (!$course->is_template && $newTeacherId && $newTeacherId != $oldTeacherId) {
            // Only teacher changed — generate lessons for new teacher
            $teacher = \App\Models\User::find($newTeacherId);
            if ($teacher) {
                $n = $scheduleService->generateCourseLessons($course, $teacher);
                if ($n > 0) $generatedMsg = " Додано {$n} занять до розкладу.";
            }
        }

        if ($request->hasFile('cover')) {
            $course->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return redirect()->route('teacher.courses.edit', $course)->with('success', 'Курс оновлено.' . $generatedMsg);
    }

    public function destroy(Course $course)
    {
        $user = auth()->user();
        $this->authorizeCourse($course);

        // Teachers cannot directly delete — they must submit a deletion request
        if ($user->isTeacher()) {
            abort(403, 'Teachers must submit a deletion request.');
        }

        $course->delete();
        return redirect()->route('teacher.courses.index')->with('success', 'Курс видалено.');
    }

    /**
     * Duplicate course from template
     */
    public function duplicate(Course $course)
    {
        $this->authorizeCourse($course);
        $newCourse = $course->duplicateAsTemplate();
        return redirect()->route('teacher.courses.edit', $newCourse)->with('success', 'Курс скопійовано.');
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

    public function addCoTeacher(Request $request, Course $course)
    {
        $this->authorizeCourse($course);
        $request->validate(['user_id' => 'required|exists:users,id']);

        $user = User::find($request->user_id);
        if (!$user || !in_array($user->role, ['teacher', 'admin', 'superadmin'])) {
            return back()->with('error', 'Користувач не є викладачем.');
        }

        if ($course->coTeachers()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Цей викладач вже є співвикладачем.');
        }

        $course->coTeachers()->attach($user->id);

        app(\App\Services\NotificationService::class)->notify(
            $user,
            'course_assigned',
            'Вас додано як співвикладача курсу',
            "Курс: {$course->title}",
            route('teacher.courses.edit', $course)
        );

        $n = app(\App\Services\ScheduleService::class)->generateCourseLessons($course, $user);
        $msg = "Викладача {$user->last_name} {$user->first_name} додано.";
        if ($n > 0) $msg .= " Додано {$n} занять до розкладу.";

        return back()->with('success', $msg);
    }

    public function removeCoTeacher(Course $course, User $user)
    {
        $this->authorizeCourse($course);
        $course->coTeachers()->detach($user->id);
        return back()->with('success', 'Співвикладача видалено.');
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

    private function scheduleChanged(Course $course, array $validated): bool
    {
        foreach (['schedule_start_time', 'schedule_end_time'] as $field) {
            if (!array_key_exists($field, $validated)) continue;
            $old = $course->getRawOriginal($field) ? substr($course->getRawOriginal($field), 0, 5) : null;
            $new = $validated[$field] ? substr((string) $validated[$field], 0, 5) : null;
            if ($old !== $new) return true;
        }
        foreach (['schedule_mode', 'schedule_location_id', 'schedule_classroom_id'] as $field) {
            if (!array_key_exists($field, $validated)) continue;
            if ((string) ($course->getRawOriginal($field) ?? '') !== (string) ($validated[$field] ?? '')) return true;
        }
        if (array_key_exists('schedule_days', $validated)) {
            $old = array_map('intval', (array) ($course->schedule_days ?? []));
            $new = array_map('intval', (array) ($validated['schedule_days'] ?? []));
            sort($old); sort($new);
            if ($old !== $new) return true;
        }
        foreach (['start_date', 'end_date'] as $field) {
            if (!array_key_exists($field, $validated)) continue;
            $old = $course->getRawOriginal($field);
            $new = $validated[$field] ? (string) $validated[$field] : null;
            if ($old !== $new) return true;
        }
        return false;
    }

    /**
     * Authorization helper
     */
    protected function authorizeCourse(Course $course): void
    {
        $user = auth()->user();
        if ($user->isSuperAdmin() || $user->isAdmin()) return;
        if ($user->isTeacher() && $course->teacher_id === $user->id) return;
        if ($user->isTeacher() && $course->coTeachers()->where('user_id', $user->id)->exists()) return;
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
