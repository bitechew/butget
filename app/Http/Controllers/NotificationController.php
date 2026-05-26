<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 25);
        $notifications = Notification::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json($notifications);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'type' => 'string|in:info,success,warning,error',
            'title' => 'required|string',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $notif = Notification::create(array_merge($data, ['user_id' => $user->id]));
        return response()->json($notif, 201);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $notification->update(['read_at' => now()]);
        return response()->json($notification);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $user = $request->user();
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $notification->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
