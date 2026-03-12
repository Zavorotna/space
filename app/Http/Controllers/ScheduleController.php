<?php

namespace App\Http\Controllers;

use App\Models\{Lesson, Location, Classroom};
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected ScheduleService $schedule;

    public function __construct(ScheduleService $schedule)
    {
        $this->schedule = $schedule;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $mode = $request->get('mode', 'week'); // day, week, month
        $date = $request->get('date', today()->toDateString());

        $lessons = match($mode) {
            'day' => $this->schedule->getDaySchedule($user, $date),
            'week' => $this->schedule->getWeekSchedule($user, $date),
            'month' => $this->schedule->getMonthSchedule($user, Carbon::parse($date)->year, Carbon::parse($date)->month),
        };

        $locations = Location::where('is_active', true)->with('classrooms')->get();

        return view('schedule.index', compact('lessons', 'mode', 'date', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'nullable|string|max:255',
            'mode' => 'required|in:online,offline',
            'location_id' => 'required_if:mode,offline|nullable|exists:locations,id',
            'classroom_id' => 'required_if:mode,offline|nullable|exists:classrooms,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $user = $request->user();
        $validated['teacher_id'] = $user->isTeacher() ? $user->id : $request->input('teacher_id', $user->id);

        try {
            $lesson = $this->schedule->createLesson($validated);
            return back()->with('success', 'Заняття додано.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'mode' => 'required|in:online,offline',
            'location_id' => 'required_if:mode,offline|nullable|exists:locations,id',
            'classroom_id' => 'required_if:mode,offline|nullable|exists:classrooms,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validated['mode'] === 'offline' && isset($validated['classroom_id'])) {
            if (Lesson::hasConflict($validated['classroom_id'], $validated['date'], $validated['start_time'], $validated['end_time'], $lesson->id)) {
                return back()->with('error', 'Конфлікт: аудиторія зайнята.');
            }
        }

        $lesson->update($validated);
        return back()->with('success', 'Заняття оновлено.');
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return back()->with('success', 'Заняття видалено.');
    }

    /**
     * Confirm attendance
     */
    public function confirmAttendance(Request $request, Lesson $lesson)
    {
        $presentIds = $request->input('present_students', []);
        $this->schedule->confirmAttendance($lesson, $presentIds);
        return back()->with('success', 'Присутність підтверджено.');
    }

    /**
     * JSON endpoint for calendar (AJAX)
     */
    public function calendarJson(Request $request)
    {
        $user = $request->user();
        $start = $request->get('start');
        $end = $request->get('end');

        $query = Lesson::with('course')
            ->whereBetween('date', [$start, $end]);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->isStudent()) {
            $courseIds = $user->activeEnrollments()->pluck('courses.id');
            $query->whereIn('course_id', $courseIds);
        }

        $lessons = $query->get()->map(fn($l) => [
            'id' => $l->id,
            'title' => $l->course->title . ($l->title ? ": {$l->title}" : ''),
            'date' => $l->date->toDateString(),
            'start_time' => $l->start_time,
            'end_time' => $l->end_time,
            'mode' => $l->mode,
        ]);

        return response()->json($lessons);
    }
}
