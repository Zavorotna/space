<?php

namespace App\Http\Controllers;

use App\Models\{PlatformNotification, PushSubscription, User};
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(PlatformNotification $notification)
    {
        if ($notification->user_id !== auth()->id()) abort(403);
        $notification->update(['is_read' => true]);
        return back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->notifications()->unread()->update(['is_read' => true]);
        return back()->with('success', 'Всі сповіщення прочитано.');
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->notifications()->unread()->count(),
        ]);
    }

    public function sendToUser(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isTeacher()) {
            abort(403);
        }

        $request->validate(['message' => 'required|string|max:1000']);

        $sender = auth()->user();

        PlatformNotification::create([
            'user_id' => $user->id,
            'type'    => 'admin_message',
            'title'   => "Повідомлення від {$sender->full_name}",
            'message' => $request->message,
            'is_read' => false,
        ]);

        return back()->with('notify_success', 'Повідомлення надіслано.');
    }

    public function dismissBanner(PlatformNotification $notification)
    {
        if ($notification->user_id !== auth()->id()) abort(403);
        $notification->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribePush(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => $request->user()->id, 'endpoint' => $validated['endpoint']],
            ['p256dh' => $validated['keys']['p256dh'], 'auth' => $validated['keys']['auth']]
        );

        return response()->json(['ok' => true]);
    }
}
