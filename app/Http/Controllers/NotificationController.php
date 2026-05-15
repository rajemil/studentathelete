<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Self-healing: If user is staff, ensure they have notifications for all current pending applications in their sports
        if (in_array($user->role, ['coach', 'instructor', 'admin'])) {
            $this->ensurePendingApplicationNotifications($user);
        }

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

    protected function ensurePendingApplicationNotifications($user)
    {
        // 1. Get all sports this user manages (pivot + instructor + coached teams)
        $assignedSportIds = $user->sports()->pluck('sports.id');
        $teamSportIds = \App\Models\Team::whereIn('id', \App\Support\CoachedTeams::teamIds($user))->pluck('sport_id');
        $instructorSportIds = \App\Models\Sport::where('instructor_user_id', $user->id)->pluck('id');
        
        $allSportIds = $assignedSportIds->merge($teamSportIds)->merge($instructorSportIds)->unique();

        if ($allSportIds->isEmpty()) return;

        // 2. Find pending applications for these sports
        $pendingApplications = \App\Models\SportApplication::whereIn('sport_id', $allSportIds)
            ->where('status', 'pending')
            ->with(['sport', 'user'])
            ->get();

        foreach ($pendingApplications as $app) {
            // 3. Robust check: Does any notification (read or unread) contain this application ID?
            // We use a broader query to avoid duplicates across different DB drivers
            $exists = $user->notifications()
                ->where('data', 'like', '%"sport_application_id":' . $app->id . '%')
                ->exists();

            if (!$exists) {
                $user->notify(new \App\Notifications\SportApplicationSubmitted($app));
            }
        }
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
