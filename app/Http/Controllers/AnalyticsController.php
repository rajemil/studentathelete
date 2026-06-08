<?php

namespace App\Http\Controllers;

use App\Actions\Analytics\GetDashboardAnalyticsAction;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly GetDashboardAnalyticsAction $dashboardAnalytics,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sport::class);

        $sportFilter = $request->filled('sport_id') ? (int) $request->input('sport_id') : null;

        return view('analytics.index', $this->dashboardAnalytics->execute(auth()->user(), $sportFilter));
    }
}
