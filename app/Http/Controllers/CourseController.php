<?php

namespace App\Http\Controllers;

use App\Models\{Course, CourseApplication, CourseReview, User};
use App\Services\{WalletService, CoinRewardService, LiqPayService};
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // ── Public pages ───────────────────────────────────────────

    /**
     * Public course listing — show published templates only
     */
    public function publicIndex()
    {
        if (auth()->check() && (auth()->user()->isTeacher() || auth()->user()->isAdmin())) {
            return redirect()->route('teacher.courses.index');
        }

        $courses = Course::where('is_template', true)
            ->where('is_published', true)
            ->with(['media', 'coTeachers'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(12);

        return view('public.courses', compact('courses'));
    }

    /**
     * Public course detail page (templates)
     */
    public function publicShow(Course $course)
    {
        if (!$course->is_published) {
            abort(404);
        }

        $course->load(['topics', 'reviews' => fn($q) => $q->where('is_approved', true)->with('user'), 'media']);

        $hasApplication = auth()->check()
            ? CourseApplication::where('course_id', $course->id)->where('user_id', auth()->id())->where('status', 'pending')->exists()
            : false;

        return view('public.course-detail', compact('course', 'hasApplication'));
    }

    // ── Student actions ────────────────────────────────────────

    /**
     * Apply for a course (template-based)
     */
    public function apply(Request $request, Course $course)
    {
        $user = $request->user();

        // Require phone number
        if (!$user->phone) {
            return back()->with('error', 'Для подачі заявки необхідно вказати номер телефону у профілі.');
        }

        if ($course->students()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Ви вже записані на цей курс.');
        }

        if (CourseApplication::where('course_id', $course->id)->where('user_id', $user->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Ваша заявка вже розглядається.');
        }

        $application = CourseApplication::create([
            'course_id' => $course->id,
            'user_id'   => $user->id,
            'note'      => $request->input('note'),
        ]);

        // Notify all possible teachers (coTeachers of template + main teacher if set)
        $notifyUsers = $course->coTeachers;
        if ($course->teacher_id) {
            $notifyUsers = $notifyUsers->push($course->teacher);
        }
        if ($notifyUsers->isEmpty()) {
            // Fallback: notify all admins
            $notifyUsers = User::whereIn('role', ['admin', 'superadmin'])->get();
        }

        $notifyUsers->unique('id')->each(function ($teacher) use ($application, $course, $user) {
            app(\App\Services\NotificationService::class)->notify(
                $teacher,
                'new_application',
                "Нова заявка на курс «{$course->title}»",
                "{$user->full_name} хоче приєднатись." . ($application->note ? " Коментар: {$application->note}" : ''),
                route('teacher.applications.show', $application)
            );
        });

        return back()->with('success', 'Заявку подано. Очікуйте підтвердження.');
    }

    /**
     * Show application detail for teacher (with join/create actions)
     */
    public function showApplication(Request $request, CourseApplication $application)
    {
        $user = $request->user();
        $template = $application->course;

        // Teacher must be possible teacher of this template or admin
        if (!$user->isAdmin()) {
            $isPossible = $template->coTeachers()->where('user_id', $user->id)->exists()
                || $template->teacher_id === $user->id;
            if (!$isPossible) abort(403);
        }

        $application->load(['user', 'course']);

        // Active courses from this template that teacher can add student to
        $existingCourses = Course::where('template_id', $template->id)
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                if (!$user->isAdmin()) {
                    $q->where('teacher_id', $user->id)
                      ->orWhereHas('coTeachers', fn($q2) => $q2->where('users.id', $user->id));
                }
            })
            ->get();

        return view('teacher.application-show', compact('application', 'existingCourses'));
    }

    /**
     * Join student to existing course (from application)
     */
    public function joinExistingCourse(Request $request, CourseApplication $application)
    {
        $request->validate(['course_id' => 'required|exists:courses,id']);
        $course = Course::findOrFail($request->course_id);

        $student = $application->user;
        $this->attachStudentToCourse($student, $course, $application);

        return redirect()->route('teacher.courses.edit', $course)->with('success', "«{$student->full_name}» додано до курсу.");
    }

    /**
     * Create course from template and add student
     */
    public function createCourseForApplication(Request $request, CourseApplication $application)
    {
        $template = $application->course;
        $newCourse = $template->duplicateAsTemplate();
        $newCourse->teacher_id = $request->user()->id;
        $newCourse->save();

        $this->attachStudentToCourse($application->user, $newCourse, $application);

        return redirect()->route('teacher.courses.edit', $newCourse)->with('success', 'Курс створено і студента додано.');
    }

    /**
     * Save application to notes (dismiss notifications without enrolling)
     */
    public function saveApplicationToNotes(Request $request, CourseApplication $application)
    {
        $user = $request->user();
        \App\Models\Note::create([
            'user_id' => $user->id,
            'content' => "Заявка на курс «{$application->course->title}» від {$application->user->full_name}."
                . ($application->note ? " Коментар: {$application->note}" : ''),
        ]);

        $this->dismissApplicationNotifications($application);
        $application->update(['status' => 'pending']); // keep pending

        return back()->with('success', 'Збережено в замітки.');
    }

    private function attachStudentToCourse(User $student, Course $course, CourseApplication $application): void
    {
        if (!$course->students()->where('user_id', $student->id)->exists()) {
            $course->students()->attach($student->id, [
                'status'      => 'active',
                'enrolled_at' => now(),
            ]);
        }

        // Auto-change role registered → student
        if ($student->role === 'registered') {
            $student->update(['role' => 'student']);
        }

        $application->update(['status' => 'approved', 'processed_by' => auth()->id()]);
        $this->dismissApplicationNotifications($application);

        // Notify student
        app(\App\Services\NotificationService::class)->notify(
            $student,
            'application_approved',
            "Заявку на курс «{$course->title}» прийнято",
            'Ви зараховані на курс. Удачі у навчанні!',
            route('courses.student.show', $course)
        );
    }

    private function dismissApplicationNotifications(CourseApplication $application): void
    {
        \App\Models\PlatformNotification::where('type', 'new_application')
            ->where('link', 'LIKE', '%/applications/' . $application->id . '%')
            ->update(['is_read' => true]);
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

            $coTeachingIds = $user->coTeacherCourses()->get()->pluck('id');
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
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'telegram_link'          => 'nullable|url',
            'is_template'            => 'boolean',
            'cover'                  => 'nullable|image|max:5120',
            'schedule_days'            => 'nullable|array',
            'schedule_days.*'          => 'integer|between:1,7',
            'schedule_times'           => 'nullable|array',
            'schedule_times.*.start'   => 'nullable|date_format:H:i',
            'schedule_times.*.end'     => 'nullable|date_format:H:i',
            'schedule_mode'            => 'nullable|in:online,offline',
            'schedule_location_id'     => 'nullable|exists:locations,id',
            'schedule_classroom_id'    => 'nullable|exists:classrooms,id',
        ]);

        $validated['teacher_id'] = $request->user()->isTeacher()
            ? $request->user()->id
            : $request->input('teacher_id', $request->user()->id);

        $course = Course::create(collect($validated)->except('cover')->toArray());

        if ($request->hasFile('cover')) {
            $course->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        $this->syncTopics($course, $request->input('topics', []));

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
        $course->load(['topics', 'homeworkAssignments', 'tests.questions', 'graduationProject', 'additionalMaterials', 'coTeachers',
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
            'status'                 => 'nullable|in:waiting,enrolling,active,completed',
            'type'                   => 'required|in:group,individual',
            'start_date'             => 'nullable|date',
            'end_date'               => 'nullable|date',
            'telegram_link'          => 'nullable|url',
            'is_published'           => 'boolean',
            'cover'                  => 'nullable|image|max:5120',
            'schedule_days'            => 'nullable|array',
            'schedule_days.*'          => 'integer|between:1,7',
            'schedule_times'           => 'nullable|array',
            'schedule_times.*.start'   => 'nullable|date_format:H:i',
            'schedule_times.*.end'     => 'nullable|date_format:H:i',
            'schedule_mode'            => 'nullable|in:online,offline',
            'schedule_location_id'     => 'nullable|exists:locations,id',
            'schedule_classroom_id'    => 'nullable|exists:classrooms,id',
        ];

        if ($request->user()->isAdmin()) {
            $rules['teacher_id'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        // Ensure schedule_days/times are explicitly null when absent (no checkboxes checked)
        if (!array_key_exists('schedule_days', $validated)) {
            $validated['schedule_days'] = null;
        }
        if (!array_key_exists('schedule_times', $validated)) {
            $validated['schedule_times'] = null;
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
            // Delete only FUTURE unstarted lessons (keep history) and regenerate from today
            $fromDate = today()->toDateString();
            \App\Models\Lesson::where('course_id', $course->id)
                ->whereNull('completion_status')
                ->where('date', '>=', $fromDate)
                ->delete();
            $total = 0;
            if ($course->teacher_id) {
                $total += $scheduleService->generateCourseLessons($course, \App\Models\User::find($course->teacher_id), $fromDate);
            }
            foreach ($course->coTeachers as $coTeacher) {
                $total += $scheduleService->generateCourseLessons($course, $coTeacher, $fromDate);
            }
            $generatedMsg = $total > 0
                ? " Розклад змінено, перегенеровано {$total} занять."
                : " Розклад змінено, майбутні незаплановані заняття видалено.";
        } elseif (!$course->is_template && $newTeacherId && $newTeacherId != $oldTeacherId) {
            $teacher = \App\Models\User::find($newTeacherId);
            if ($teacher) {
                $n = $scheduleService->generateCourseLessons($course, $teacher);
                if ($n > 0) $generatedMsg = " Додано {$n} занять до розкладу.";
            }
        }

        if ($request->hasFile('cover')) {
            $course->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        $this->syncTopics($course, $request->input('topics', []));

        return redirect()->route('teacher.courses.edit', $course)->with('success', 'Курс оновлено.' . $generatedMsg);
    }

    public function updateTeacher(Request $request, Course $course)
    {
        $this->authorizeCourse($course);
        if (!$request->user()->isAdmin()) abort(403);

        $validated = $request->validate(['teacher_id' => 'required|exists:users,id']);
        $oldId = $course->teacher_id;
        $course->update(['teacher_id' => $validated['teacher_id']]);

        if ($validated['teacher_id'] != $oldId) {
            $teacher = \App\Models\User::find($validated['teacher_id']);
            if ($teacher) {
                app(\App\Services\NotificationService::class)->notify(
                    $teacher, 'course_assigned', 'Вас призначено викладачем курсу',
                    "Курс: {$course->title}", route('teacher.courses.edit', $course)
                );
            }
        }

        return back()->with('success', 'Викладача змінено.');
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

        $request->validate([
            'user_id' => 'nullable|integer|min:1',
            'phone'   => 'nullable|string|max:20',
        ]);

        $userId = $request->input('user_id');

        if (!$userId && $request->filled('phone')) {
            $phone = $request->input('phone');
            $student = User::where('phone', $phone)
                ->orWhere('phone', preg_replace('/\D/', '', $phone))
                ->first();
            if (!$student) {
                return back()->with('error', 'Студента з таким номером телефону не знайдено.');
            }
            $userId = $student->id;
        }

        if (!$userId) {
            return back()->with('error', 'Вкажіть ID або номер телефону студента.');
        }

        if ($course->students()->where('user_id', $userId)->exists()) {
            return back()->with('error', 'Студент вже на курсі.');
        }

        $course->students()->attach($userId, [
            'status' => 'active',
            'enrolled_at' => now(),
            'active_until' => now()->addYear(),
        ]);

        $student = User::find($userId);
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

    private function syncTopics(Course $course, array $submitted): void
    {
        $keepIds = collect();
        foreach ($submitted as $idx => $data) {
            if (empty(trim($data['title'] ?? ''))) continue;
            $id = !empty($data['id']) ? (int) $data['id'] : null;
            if ($id) {
                $topic = $course->topics()->find($id);
                if ($topic) {
                    $topic->update(['title' => trim($data['title']), 'sort_order' => $idx + 1]);
                    $keepIds->push($id);
                    continue;
                }
            }
            $new = $course->topics()->create(['title' => trim($data['title']), 'sort_order' => $idx + 1]);
            $keepIds->push($new->id);
        }
        $course->topics()->whereNotIn('id', $keepIds)->delete();
    }

    private function scheduleChanged(Course $course, array $validated): bool
    {
        // Per-day times
        if (array_key_exists('schedule_times', $validated)) {
            $norm = function ($times) {
                $r = [];
                foreach ((array) $times as $day => $t) {
                    $r[(string) $day] = ['start' => $t['start'] ?? '', 'end' => $t['end'] ?? ''];
                }
                ksort($r);
                return $r;
            };
            if ($norm($course->schedule_times ?? []) !== $norm($validated['schedule_times'] ?? [])) return true;
        }
        // Mode / location / classroom
        foreach (['schedule_mode', 'schedule_location_id', 'schedule_classroom_id'] as $field) {
            if (!array_key_exists($field, $validated)) continue;
            if ((string) ($course->getRawOriginal($field) ?? '') !== (string) ($validated[$field] ?? '')) return true;
        }
        // Days
        if (array_key_exists('schedule_days', $validated)) {
            $old = array_map('intval', (array) ($course->schedule_days ?? []));
            $new = array_map('intval', (array) ($validated['schedule_days'] ?? []));
            sort($old); sort($new);
            if ($old !== $new) return true;
        }
        // Dates
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
