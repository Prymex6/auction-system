<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $unreadOnly = $request->get('unread_only', false);

        $query = $request->user()->notifications()
            ->orderByDesc('created_at');

        if ($unreadOnly) {
            $query->where('read', false);
        }

        $notifications = $query->paginate($limit);

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'unread_count' => $request->user()->notifications()->where('read', false)->count(),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        NotificationService::markAsRead($notification);

        return response()->json([
            'message' => 'Powiadomienie oznaczone jako przeczytane',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        NotificationService::markAllAsRead($request->user());

        return response()->json([
            'message' => 'Wszystkie powiadomienia oznaczone jako przeczytane',
        ]);
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->notifications()->where('read', false)->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }

    public function destroy(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Powiadomienie usunięte',
        ]);
    }
}
