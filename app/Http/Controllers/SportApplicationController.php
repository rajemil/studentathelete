<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\SportApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SportApplicationController extends Controller
{
    public function approve(Request $request, Sport $sport, SportApplication $application): RedirectResponse
    {
        $this->authorize('assignStudents', $sport);
        abort_unless((int) $application->sport_id === (int) $sport->id, 404);
        abort_unless($application->status === 'pending', 422);

        $application->user->sports()->syncWithoutDetaching([$sport->id]);
        $application->update(['status' => 'approved']);

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Application approved and student assigned.');
    }

    public function reject(Request $request, Sport $sport, SportApplication $application): RedirectResponse
    {
        $this->authorize('assignStudents', $sport);
        abort_unless((int) $application->sport_id === (int) $sport->id, 404);
        abort_unless($application->status === 'pending', 422);

        $application->update(['status' => 'rejected']);

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Application rejected.');
    }
}
