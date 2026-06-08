<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\Sport\SportResolutionService;
use Illuminate\View\View;

class StaffPerformanceScoresHubController extends Controller
{
    public function __invoke(SportResolutionService $sportResolver): View
    {
        $sports = $sportResolver->sportsForActor(auth()->user());

        return view('staff.performance-scores-hub', compact('sports'));
    }
}
