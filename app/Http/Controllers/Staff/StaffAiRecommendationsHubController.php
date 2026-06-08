<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\Sport\SportResolutionService;
use Illuminate\View\View;

class StaffAiRecommendationsHubController extends Controller
{
    public function __invoke(SportResolutionService $sportResolver): View
    {
        $sports = $sportResolver->sportsForActor(auth()->user());

        return view('staff.ai-recommendations-hub', compact('sports'));
    }
}
