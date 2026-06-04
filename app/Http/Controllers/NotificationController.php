<?php

namespace App\Http\Controllers;

use App\Models\SportApplication;
use App\Models\User;
use App\Services\Staff\PendingSportApplicationsCount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401);
        }

        // Self-healing: If user is staff, ensure they have notifications for all current pending applications in their sports
        if (in_array($user->role, ['coach', 'instructor', 'admin'], true)) {
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

    protected function ensurePendingApplicationNotifications(User $user): void
    {
        if ($user->role === 'admin') {
            return;
        }

        $sportIds = PendingSportApplicationsCount::managedSportIds($user);
        if ($sportIds->isEmpty()) {
            return;
        }

        // Process in chunks — never load all pending rows + relations into memory.
        SportApplication::query()
            ->where('status', 'pending')
            ->whereIn('sport_id', $sportIds)
            ->select(['id', 'sport_id', 'user_id', 'status'])
            ->orderBy('id')
            ->chunkById(50, function ($applications) use ($user): void {
                foreach ($applications as $application) {
                    $exists = $user->notifications()
                        ->where('data', 'like', '%"sport_application_id":'.$application->id.'%')
                        ->exists();

                    if (! $exists) {
                        $application->loadMissing(['sport:id,slug,name', 'user:id,name']);
                        $user->notify(new \App\Notifications\SportApplicationSubmitted($application));
                    }
                }
            });
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
