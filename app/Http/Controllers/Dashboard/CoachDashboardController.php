<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Concerns\BuildsCoachStyleDashboard;
use App\Services\Insights\InsightsService;
use Illuminate\View\View;

class CoachDashboardController extends Controller
{
    use BuildsCoachStyleDashboard;

    public function __invoke(InsightsService $insightsService): View
    {
        $user = auth()->user();

        return view('dashboards.coach', $this->coachStyleDashboardPayload($user, $insightsService));
    }
}
