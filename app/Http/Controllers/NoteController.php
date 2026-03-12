<?php

namespace App\Http\Controllers;

use App\Models\{Note, User};
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $personalNotes = Note::personal($user->id)->latest()->get();
        $receivedNotes = Note::receivedBy($user->id)->with('author')->latest()->get();

        return view('notes.index', compact('personalNotes', 'receivedNotes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'recipient_id' => 'nullable|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $note = Note::create([
            'user_id' => $request->user()->id,
            'recipient_id' => $validated['recipient_id'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'content' => $validated['content'],
        ]);

        // If teacher sends note to student, notify
        if ($note->recipient_id) {
            $recipient = User::find($note->recipient_id);
            app(NotificationService::class)->notify(
                $recipient, 'note',
                'Нова замітка від викладача',
                \Illuminate\Support\Str::limit($note->content, 100),
                route('notes.index')
            );

            // Notify parents if student has remark-like note
            if ($recipient->isStudent()) {
                app(NotificationService::class)->remarkNotification($recipient, $note->content);
            }
        }

        return back()->with('success', 'Замітку збережено.');
    }

    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== $request->user()->id) abort(403);
        $note->update($request->validate(['content' => 'required|string|max:5000']));
        return back()->with('success', 'Замітку оновлено.');
    }

    public function destroy(Note $note)
    {
        if ($note->user_id !== auth()->id()) abort(403);
        $note->delete();
        return back()->with('success', 'Замітку видалено.');
    }

    public function markRead(Note $note)
    {
        if ($note->recipient_id !== auth()->id()) abort(403);
        $note->update(['is_read' => true]);
        return back();
    }
}
