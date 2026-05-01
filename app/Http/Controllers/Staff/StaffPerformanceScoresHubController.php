<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\Team;
use App\Support\CoachedTeams;
use Illuminate\View\View;

class StaffPerformanceScoresHubController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $teamIds = CoachedTeams::teamIds($user);

        $sportIds = Team::query()
            ->whereIn('id', $teamIds)
            ->pluck('sport_id')
            ->unique()
            ->filter();

        $sports = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->whereIn('id', $sportIds)
            ->orderBy('name')
            ->get();

        return view('staff.performance-scores-hub', compact('sports'));
    }
}
