<?php

namespace App\Http\Controllers;

use App\Helpers\OptimizationHelper;
use App\Http\Requests\MarkNotificationReadRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(20),
            'unreadCount' => $this->rememberUnreadCount($user),
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $this->rememberUnreadCount($user),
            'items' => $user->unreadNotifications()->latest()->limit(8)->get()->map(function ($notification): array {
                return [
                    'id' => $notification->id,
                    'message' => $notification->data['message'] ?? 'Notification',
                    'created_at' => $notification->created_at?->diffForHumans(),
                ];
            }),
        ]);
    }

    public function markRead(MarkNotificationReadRequest $request): JsonResponse
    {
        $user = $request->user();
        $ids = $request->validated('notification_ids');

        if (empty($ids)) {
            $user->unreadNotifications->markAsRead();
        } else {
            $user->unreadNotifications()->whereIn('id', $ids)->update(['read_at' => now()]);
        }

        OptimizationHelper::forgetNotificationUnreadCount($user->id);

        return response()->json(['message' => 'Notifications marked as read.']);
    }

    /**
     * Cheap guard for navbar polling — invalidated when notifications are marked read.
     */
    private function rememberUnreadCount(User $user): int
    {
        $ttlSeconds = (int) config('mowing.cache.ttl.notification_unread_seconds', 20);

        return Cache::remember(
            OptimizationHelper::notificationUnreadKey($user->id),
            $ttlSeconds,
            fn (): int => (int) $user->unreadNotifications()->count()
        );
    }
}
