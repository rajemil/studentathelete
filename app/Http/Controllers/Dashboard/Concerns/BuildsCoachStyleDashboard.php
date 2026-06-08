<?php

namespace App\Http\Controllers\Dashboard\Concerns;

use App\Models\Event;
use App\Models\Insight;
use App\Models\PerformanceScore;
use App\Models\Team;
use App\Models\User;
use App\Services\Analytics\AnalyticsCache;
use App\Services\Insights\InsightsService;
use App\Support\CoachedTeams;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait BuildsCoachStyleDashboard
{
    /**
     * @return array{kpi: array, teams: Collection, recentEvents: Collection, chart: array, insights: Collection, riskyAthletes: Collection}
     */
    protected function coachStyleDashboardPayload(User $user, InsightsService $insightsService): array
    {
        return AnalyticsCache::remember(
            AnalyticsCache::dashboardPayloadKey((int) $user->id),
            fn () => $this->buildCoachStyleDashboardPayload($user, $insightsService),
            300,
        );
    }

    /**
     * @return array{kpi: array, teams: Collection, recentEvents: Collection, chart: array, insights: Collection, riskyAthletes: Collection}
     */
    private function buildCoachStyleDashboardPayload(User $user, InsightsService $insightsService): array
    {
        $now = CarbonImmutable::now();

        $insightsService->ensureGenerated($now);

        $athleteIds = CoachedTeams::coachedStudentIds($user);

        $athletes = User::query()
            ->whereIn('id', $athleteIds)
            ->with(['profile'])
            ->orderBy('name')
            ->get();

        $kpi = [
            'athletes' => $athleteIds->count(),
            'events_upcoming' => Event::query()
                ->where('sport_id', $user->sport_id)
                ->whereNotNull('starts_at')
                ->where('starts_at', '>=', $now)
                ->count(),
        ];

        $recentEvents = Event::query()
            ->where('sport_id', $user->sport_id)
            ->orderByDesc('starts_at')
            ->limit(5)
            ->get();

        $sportPerformance = PerformanceScore::query()
            ->where('sport_id', $user->sport_id)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $now->subDays(30)->toDateString())
            ->orderBy('scored_on')
            ->get(['scored_on', 'score'])
            ->groupBy(fn ($row) => (string) $row->scored_on)
            ->map(fn ($rows) => round($rows->avg('score'), 2))
            ->take(30);

        $chart = [
            'teamPerformance' => [
                'labels' => $sportPerformance->keys()->values(),
                'values' => $sportPerformance->values(),
            ],
        ];

        $orgId = (int) $user->organization_id;

        $insights = Insight::query()
            ->where(function ($q) use ($athleteIds, $orgId) {
                $q->whereIn('user_id', $athleteIds)
                    ->orWhere(function ($q2) use ($orgId) {
                        $q2->where('type', 'narrative_summary')
                            ->where('organization_id', $orgId)
                            ->whereNull('user_id');
                    });
            })
            ->orderByDesc('computed_at')
            ->limit(6)
            ->get();

        $riskyAthletes = User::query()
            ->whereIn('id', $athleteIds)
            ->whereHas('profile', fn ($q) => $q->whereIn('injury_risk', ['high', 'medium']))
            ->with('profile')
            ->limit(6)
            ->get();

        return compact('kpi', 'athletes', 'recentEvents', 'chart', 'insights', 'riskyAthletes');
    }
}
