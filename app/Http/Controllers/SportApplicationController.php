<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\SportApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SportApplicationController extends Controller
{
    public function review(Sport $sport, SportApplication $application)
    {
        $this->authorize('assignStudents', $sport);
        abort_unless((int) $application->sport_id === (int) $sport->id, 404);

        return view('sports.applications.review', compact('sport', 'application'));
    }

    public function approve(Request $request, Sport $sport, SportApplication $application): RedirectResponse
    {
        $this->authorize('assignStudents', $sport);
        abort_unless((int) $application->sport_id === (int) $sport->id, 404);
        abort_unless($application->status === 'pending', 422);

        $application->update(['status' => 'approved']);
        $sport->students()->syncWithoutDetaching($application->user_id);

        \App\Services\Analytics\AnalyticsCache::forgetCoachDashboardsForSports(
            [(int) $sport->id],
            (int) $request->user()->organization_id,
        );

        $application->user->notify(new \App\Notifications\SportApplicationStatusChanged($application, 'approved'));

        return $this->redirectRoutePreservingModal($request, 'sports.show', $sport)
            ->with('status', 'Application approved and student assigned.');
    }

    public function reject(Request $request, Sport $sport, SportApplication $application): RedirectResponse
    {
        $this->authorize('assignStudents', $sport);
        abort_unless((int) $application->sport_id === (int) $sport->id, 404);
        abort_unless($application->status === 'pending', 422);

        $application->update(['status' => 'rejected']);

        $application->user->notify(new \App\Notifications\SportApplicationStatusChanged($application, 'rejected'));

        return $this->redirectRoutePreservingModal($request, 'sports.show', $sport)
            ->with('status', 'Application rejected.');
    }
}
