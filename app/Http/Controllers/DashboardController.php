<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Course, Lesson, Transaction, MonthlyLeaderboard, Location, CalendarEvent};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match($user->role) {
            'superadmin', 'admin' => $this->adminDashboard($user, $request),
            'teacher' => $this->teacherDashboard($user, $request),
            'student' => $this->studentDashboard($user, $request),
            'parent' => $this->parentDashboard($user),
            'registered' => redirect()->route('courses.public'),
            default => redirect()->route('home'),
        };
    }

    private function scheduleRange(Carbon $date, string $mode): array
    {
        return match($mode) {
            'week'  => [$date->copy()->startOfWeek()->toDateString(), $date->copy()->endOfWeek()->toDateString()],
            'month' => [$date->copy()->startOfMonth()->toDateString(), $date->copy()->endOfMonth()->toDateString()],
            default => [$date->toDateString(), $date->toDateString()],
        };
    }

    protected function adminDashboard($user, Request $request)
    {
        $schedMode = $request->get('schedule_mode', 'day');
        $schedDate = Carbon::parse($request->get('schedule_date', today()));
        [$start, $end] = $this->scheduleRange($schedDate, $schedMode);

        $schedLessons = Lesson::with(['course', 'teacher', 'location', 'classroom'])
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $schedEvents = CalendarEvent::whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $schedLocations = Location::where('is_active', true)->with('classrooms')->get();
        $schedCourses   = Course::where('status', 'active')->where('is_template', false)->get();

        $lessonsNeedingReport = Lesson::with(['course'])
            ->where('teacher_id', $user->id)
            ->where(function ($q) {
                $q->where('date', '<', today())
                  ->orWhere(function ($q2) {
                      $q2->where('date', today())
                         ->where('end_time', '<=', now()->format('H:i:s'));
                  });
            })
            ->whereNull('completion_status')
            ->orderBy('date', 'desc')
            ->orderBy('end_time', 'desc')
            ->limit(10)
            ->get();

        $data = [
            'pendingApplications'   => \App\Models\CourseApplication::where('status', 'pending')->count(),
            'pendingWithdrawalsList'=> \App\Models\WithdrawalRequest::with('user')->where('status', 'pending')->latest()->get(),
            'recentTransactions'    => Transaction::with('user')->latest()->limit(20)->get(),
            'lessonsNeedingReport'  => $lessonsNeedingReport,
            'schedDate'             => $schedDate,
            'schedMode'             => $schedMode,
            'schedLessons'          => $schedLessons,
            'schedEvents'           => $schedEvents,
            'schedLocations'        => $schedLocations,
            'schedCourses'          => $schedCourses,
        ];

        return view('admin.dashboard', $data);
    }

    protected function teacherDashboard($user, Request $request)
    {
        $schedMode = $request->get('schedule_mode', 'day');
        $schedDate = Carbon::parse($request->get('schedule_date', today()));
        [$start, $end] = $this->scheduleRange($schedDate, $schedMode);

        $schedLessons = Lesson::with(['course', 'location', 'classroom'])
            ->where(function ($q) use ($user) {
                $q->where('teacher_id', $user->id)
                  ->orWhereHas('course.coTeachers', fn($q2) => $q2->where('users.id', $user->id));
            })
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $schedEvents = CalendarEvent::whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $schedLocations = Location::where('is_active', true)->with('classrooms')->get();

        $schedCourses = Course::where(function ($q) use ($user) {
            $q->where('teacher_id', $user->id)
              ->orWhereHas('coTeachers', fn($q2) => $q2->where('users.id', $user->id));
        })->where('status', 'active')->where('is_template', false)->get();

        $courses = $user->taughtCourses()->with('activeStudents')->where('status', 'active')->get();

        $pendingHomework = \App\Models\HomeworkSubmission::whereHas('homework', function ($q) use ($user) {
                $q->whereIn('course_id', $user->taughtCourses()->pluck('id'));
            })
            ->where('status', 'submitted')
            ->count();

        $lessonsNeedingReport = Lesson::with(['course'])
            ->where('teacher_id', $user->id)
            ->where(function ($q) {
                $q->where('date', '<', today())
                  ->orWhere(function ($q2) {
                      $q2->where('date', today())
                         ->where('end_time', '<=', now()->format('H:i:s'));
                  });
            })
            ->whereNull('completion_status')
            ->orderBy('date', 'desc')
            ->orderBy('end_time', 'desc')
            ->limit(10)
            ->get();

        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        $notes = $user->notes()->whereNull('recipient_id')->latest()->limit(5)->get();

        return view('teacher.dashboard', compact(
            'courses', 'pendingHomework', 'lessonsNeedingReport',
            'wallet', 'transactions', 'notes',
            'schedDate', 'schedMode', 'schedLessons', 'schedEvents', 'schedLocations', 'schedCourses'
        ));
    }

    protected function studentDashboard($user, Request $request)
    {
        $schedMode = $request->get('schedule_mode', 'day');
        $schedDate = Carbon::parse($request->get('schedule_date', today()));
        [$start, $end] = $this->scheduleRange($schedDate, $schedMode);

        $courseIds = $user->activeEnrollments()->pluck('courses.id');

        $schedLessons = Lesson::with(['course', 'location'])
            ->whereIn('course_id', $courseIds)
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $schedEvents = CalendarEvent::whereBetween('date', [$start, $end])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $currentCourse = $user->activeEnrollments()->with('teacher')->first();

        $pendingHomework = \App\Models\HomeworkSubmission::where('user_id', $user->id)
            ->where('status', 'revision')->count();
        $totalHomeworkToDo = \App\Models\HomeworkAssignment::whereIn('course_id', $courseIds)
            ->whereDoesntHave('submissions', fn($q) => $q->where('user_id', $user->id)->where('status', 'accepted'))
            ->count();

        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        $notes = $user->notes()->whereNull('recipient_id')->latest()->limit(5)->get();
        $receivedNotes = $user->receivedNotes()->with('author')->unread()->get();

        return view('student.dashboard', compact(
            'currentCourse', 'pendingHomework', 'totalHomeworkToDo',
            'wallet', 'transactions', 'notes', 'receivedNotes',
            'schedDate', 'schedMode', 'schedLessons', 'schedEvents'
        ));
    }

    protected function parentDashboard($user)
    {
        $children = $user->children()->with([
            'activeEnrollments',
            'activeEnrollments.teacher',
        ])->get();

        $childrenData = [];
        foreach ($children as $child) {
            $childrenData[] = [
                'child' => $child,
                'courses' => $child->activeEnrollments,
                'recentAttendances' => \App\Models\Attendance::where('user_id', $child->id)
                    ->with('lesson.course')
                    ->latest()->limit(10)->get(),
                'notes' => \App\Models\Note::where('recipient_id', $child->id)
                    ->with('author')
                    ->latest()->limit(10)->get(),
            ];
        }

        return view('parent.dashboard', compact('childrenData'));
    }
}
