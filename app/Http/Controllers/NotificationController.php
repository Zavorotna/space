<?php

namespace App\Http\Controllers;

use App\Models\{PlatformNotification, PushSubscription};
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
