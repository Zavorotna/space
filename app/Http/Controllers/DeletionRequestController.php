<?php

namespace App\Http\Controllers;

use App\Models\{DeletionRequest, PlatformNotification, User, Course};
use Illuminate\Http\Request;

class DeletionRequestController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'reason'        => 'nullable|string|max:1000',
            'deletable_type' => 'required|string|in:App\Models\Course,App\Models\Test',
            'deletable_id'  => 'required|integer',
        ]);

        $type = $request->deletable_type;
        $id   = (int) $request->deletable_id;

        $deletable = $type::findOrFail($id);

        // Teachers can only request deletion of their own items
        if ($user->isTeacher()) {
            $ownsIt = match($type) {
                \App\Models\Course::class => $deletable->teacher_id === $user->id,
                \App\Models\Test::class   => $deletable->course?->teacher_id === $user->id,
                default                   => false,
            };
            if (!$ownsIt) abort(403);
        }

        // Prevent duplicate pending requests
        if (DeletionRequest::where('deletable_type', $type)
            ->where('deletable_id', $id)
            ->pending()
            ->exists()) {
            return back()->with('deletion_pending', 'Запит на видалення вже надіслано і очікує розгляду.');
        }

        $dr = DeletionRequest::create([
            'requester_id'  => $user->id,
            'reason'        => $request->reason,
            'deletable_type' => $type,
            'deletable_id'  => $id,
            'status'        => 'pending',
        ]);

        $itemName = $deletable->title ?? $deletable->name ?? "#{$id}";
        $typeLabel = $type === \App\Models\Course::class ? 'курс' : 'тест';

        // Notify all admins/superadmins
        User::whereIn('role', ['admin', 'superadmin'])->each(function ($admin) use ($dr, $user, $itemName, $typeLabel) {
            PlatformNotification::create([
                'user_id'             => $admin->id,
                'type'                => 'deletion_request',
                'title'               => "Запит на видалення: {$typeLabel} «{$itemName}»",
                'message'             => "Від {$user->full_name}" . ($dr->reason ? ": {$dr->reason}" : ''),
                'deletion_request_id' => $dr->id,
                'is_read'             => false,
            ]);
        });

        return back()->with('deletion_requested', 'Запит на видалення надіслано адміністраторам. Елемент приховано до отримання рішення.');
    }

    public function approve(Request $request, DeletionRequest $deletionRequest)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        if ($deletionRequest->status !== 'pending') {
            return response()->json(['error' => 'Запит вже оброблено.'], 422);
        }

        $deletable = $deletionRequest->deletable;
        if ($deletable) {
            $deletable->delete();
        }

        $deletionRequest->update([
            'status'       => 'approved',
            'processed_by' => auth()->id(),
        ]);

        // Mark all related notifications as read (dismiss from all dashboards)
        PlatformNotification::where('deletion_request_id', $deletionRequest->id)
            ->update(['is_read' => true]);

        // Notify teacher of decision
        $approvedTitle = $deletionRequest->deletable?->title ?? 'запис';
        PlatformNotification::create([
            'user_id' => $deletionRequest->requester_id,
            'type'    => 'deletion_approved',
            'title'   => 'Запит на видалення підтверджено',
            'message' => "Адміністратор підтвердив видалення: «{$approvedTitle}»",
            'is_read' => false,
        ]);

        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, DeletionRequest $deletionRequest)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        if ($deletionRequest->status !== 'pending') {
            return response()->json(['error' => 'Запит вже оброблено.'], 422);
        }

        $itemTitle = $deletionRequest->deletable?->title ?? 'запис';

        $deletionRequest->update([
            'status'       => 'rejected',
            'processed_by' => auth()->id(),
        ]);

        // Mark all related notifications as read
        PlatformNotification::where('deletion_request_id', $deletionRequest->id)
            ->update(['is_read' => true]);

        // Notify teacher
        PlatformNotification::create([
            'user_id' => $deletionRequest->requester_id,
            'type'    => 'deletion_rejected',
            'title'   => 'Запит на видалення відхилено',
            'message' => "Адміністратор відхилив запит на видалення: «{$itemTitle}». Елемент відновлено.",
            'is_read' => false,
        ]);

        return response()->json(['ok' => true]);
    }

    // Superadmin: permanently purge the request and all its notifications from DB
    public function destroy(DeletionRequest $deletionRequest)
    {
        if (!auth()->user()->isSuperAdmin()) abort(403);

        $itemTitle = $deletionRequest->deletable?->title ?? 'запис';

        // Notify teacher that request was cancelled
        PlatformNotification::create([
            'user_id' => $deletionRequest->requester_id,
            'type'    => 'deletion_rejected',
            'title'   => 'Запит на видалення скасовано',
            'message' => "Суперадмін скасував запит на видалення: «{$itemTitle}». Елемент відновлено.",
            'is_read' => false,
        ]);

        // Deleting the DR cascades to delete all related PlatformNotifications
        $deletionRequest->delete();

        return response()->json(['ok' => true]);
    }
}