<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'read_at' => optional($n->read_at)?->toISOString(),
                'created_at' => optional($n->created_at)?->toISOString(),
                'data' => $n->data,
            ]);

        return response()->json([
            'unread_count' => (int) $user->unreadNotifications()->count(),
            'notifications' => $items,
        ]);
    }

    public function read(Request $request, string $id): JsonResponse
    {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['ok' => true]);
    }
}
