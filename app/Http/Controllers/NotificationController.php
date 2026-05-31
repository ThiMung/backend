<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = Notification::query()
            ->with('event:id,title,start_time,location,status')
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(30)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    public function unreadCount(): JsonResponse
    {
        $count = Notification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        $this->authorizeNotification($notification);

        if ($notification->read_at === null) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json(['notification' => $notification->fresh('event')]);
    }

    public function markAllAsRead(): JsonResponse
    {
        Notification::query()
            ->where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    private function authorizeNotification(Notification $notification): void
    {
        abort_if($notification->user_id !== Auth::id(), 403, 'You cannot access this notification.');
    }
}
