<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Course, Lesson, Transaction, MonthlyLeaderboard};
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match($user->role) {
            'superadmin', 'admin' => $this->adminDashboard($user),
            'teacher' => $this->teacherDashboard($user),
            'student' => $this->studentDashboard($user),
            'parent' => $this->parentDashboard($user),
            'registered' => redirect()->route('courses.public'),
            default => redirect()->route('home'),
        };
    }

    protected function adminDashboard($user)
    {
        $data = [
            'totalStudents' => \App\Models\User::where('role', 'student')->count(),
            'activeCourses' => Course::where('status', 'active')->count(),
            'pendingApplications' => \App\Models\CourseApplication::where('status', 'pending')->count(),
            'pendingWithdrawals' => \App\Models\WithdrawalRequest::where('status', 'pending')->count(),
            'recentTransactions' => Transaction::with('user')->latest()->limit(20)->get(),
            'todayLessons' => Lesson::with(['course', 'teacher'])->where('date', today())->orderBy('start_time')->get(),
        ];

        return view('admin.dashboard', $data);
    }

    protected function teacherDashboard($user)
    {
        $todayLessons = Lesson::with('course')
            ->where('teacher_id', $user->id)
            ->where('date', today())
            ->orderBy('start_time')
            ->get();

        $courses = $user->taughtCourses()->with('activeStudents')->where('status', 'active')->get();

        $weekSchedule = Lesson::where('teacher_id', $user->id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->orderBy('date')->orderBy('start_time')
            ->get();

        $pendingHomework = \App\Models\HomeworkSubmission::whereHas('homework', function ($q) use ($user) {
                $q->whereIn('course_id', $user->taughtCourses()->pluck('id'));
            })
            ->where('status', 'submitted')
            ->count();

        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        $notes = $user->notes()->whereNull('recipient_id')->latest()->limit(5)->get();

        return view('teacher.dashboard', compact(
            'todayLessons', 'courses', 'weekSchedule', 'pendingHomework',
            'wallet', 'transactions', 'notes'
        ));
    }

    protected function studentDashboard($user)
    {
        $currentCourse = $user->activeEnrollments()->with('teacher')->first();
        $schedule = Lesson::whereIn('course_id', $user->activeEnrollments()->pluck('courses.id'))
            ->where('date', '>=', today())
            ->orderBy('date')->orderBy('start_time')
            ->limit(10)->get();

        $pendingHomework = \App\Models\HomeworkSubmission::where('user_id', $user->id)
            ->where('status', 'revision')->count();
        $totalHomeworkToDo = \App\Models\HomeworkAssignment::whereIn('course_id', $user->activeEnrollments()->pluck('courses.id'))
            ->whereDoesntHave('submissions', fn($q) => $q->where('user_id', $user->id)->where('status', 'accepted'))
            ->count();

        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        $notes = $user->notes()->whereNull('recipient_id')->latest()->limit(5)->get();
        $receivedNotes = $user->receivedNotes()->with('author')->unread()->get();

        return view('student.dashboard', compact(
            'currentCourse', 'schedule', 'pendingHomework', 'totalHomeworkToDo',
            'wallet', 'transactions', 'notes', 'receivedNotes'
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
