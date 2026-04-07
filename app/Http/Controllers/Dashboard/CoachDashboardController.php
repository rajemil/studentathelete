<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Insight;
use App\Models\PerformanceScore;
use App\Models\Team;
use App\Models\User;
use App\Services\Insights\InsightsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CoachDashboardController extends Controller
{
    public function __invoke(InsightsService $insightsService): View
    {
        $user = auth()->user();
        $now = CarbonImmutable::now();

        $insightsService->ensureGenerated($now);

        $teamIds = Team::query()
            ->where('primary_coach_id', $user->id)
            ->orWhereHas('coachAssignments', fn ($q) => $q->where('coach_id', $user->id))
            ->pluck('id');

        $teams = Team::query()
            ->whereIn('id', $teamIds)
            ->with(['sport', 'students' => fn ($q) => $q->limit(8)])
            ->orderBy('name')
            ->get();

        $kpi = [
            'teams' => (int) $teamIds->count(),
            'athletes' => (int) DB::table('team_memberships')->whereIn('team_id', $teamIds)->distinct('user_id')->count('user_id'),
            'events_upcoming' => Event::query()->whereIn('team_id', $teamIds)->whereNotNull('starts_at')->where('starts_at', '>=', $now)->count(),
        ];

        $recentEvents = Event::query()
            ->whereIn('team_id', $teamIds)
            ->orderByDesc('starts_at')
            ->limit(5)
            ->get();

        $teamPerformance = PerformanceScore::query()
            ->whereIn('team_id', $teamIds)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $now->subDays(30)->toDateString())
            ->orderBy('scored_on')
            ->get(['team_id', 'scored_on', 'score'])
            ->groupBy(fn ($row) => (string) $row->scored_on)
            ->map(fn ($rows) => round($rows->avg('score'), 2))
            ->take(30);

        $chart = [
            'teamPerformance' => [
                'labels' => $teamPerformance->keys()->values(),
                'values' => $teamPerformance->values(),
            ],
        ];

        $athleteIds = DB::table('team_memberships')
            ->whereIn('team_id', $teamIds)
            ->distinct('user_id')
            ->pluck('user_id');

        $insights = Insight::query()
            ->whereIn('user_id', $athleteIds)
            ->orWhereNull('user_id')
            ->orderByDesc('computed_at')
            ->limit(6)
            ->get();

        $riskyAthletes = User::query()
            ->whereIn('id', $athleteIds)
            ->whereHas('profile', fn ($q) => $q->whereIn('injury_risk', ['high', 'medium']))
            ->with('profile')
            ->limit(6)
            ->get();

        return view('dashboards.coach', compact('kpi', 'teams', 'recentEvents', 'chart', 'insights', 'riskyAthletes'));
    }
}

