<?php

namespace App\Http\Controllers\Dashboard\Concerns;

use App\Models\Event;
use App\Models\Insight;
use App\Models\PerformanceScore;
use App\Models\User;
use App\Services\Analytics\AnalyticsCache;
use App\Services\Insights\InsightsService;
use App\Services\Sport\SportResolutionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

trait BuildsCoachStyleDashboard
{
    /**
     * @return array{kpi: array, athletes: Collection, recentEvents: Collection, chart: array, insights: Collection, riskyAthletes: Collection}
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
     * @return array{kpi: array, athletes: Collection, recentEvents: Collection, chart: array, insights: Collection, riskyAthletes: Collection}
     */
    private function buildCoachStyleDashboardPayload(User $user, InsightsService $insightsService): array
    {
        $now = CarbonImmutable::now();
        $sportResolver = app(SportResolutionService::class);

        $insightsService->ensureGenerated($now);

        $athleteIds = $sportResolver->coachedStudentIds($user);
        $sportIds = $sportResolver->coachSportIds($user);

        $athletes = User::query()
            ->whereIn('id', $athleteIds)
            ->with(['profile'])
            ->orderBy('name')
            ->get();

        $eventsQuery = Event::query()
            ->whereNotNull('starts_at');

        if ($sportIds->isNotEmpty()) {
            $eventsQuery->whereIn('sport_id', $sportIds);
        } else {
            $eventsQuery->whereRaw('1 = 0');
        }

        $kpi = [
            'athletes' => $athleteIds->count(),
            'events_upcoming' => (clone $eventsQuery)
                ->where('starts_at', '>=', $now)
                ->count(),
        ];

        $recentEvents = (clone $eventsQuery)
            ->orderByDesc('starts_at')
            ->limit(5)
            ->get();

        $performanceQuery = PerformanceScore::query()
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $now->subDays(30)->toDateString());

        if ($sportIds->isNotEmpty()) {
            $performanceQuery->whereIn('sport_id', $sportIds);
        } else {
            $performanceQuery->whereRaw('1 = 0');
        }

        if ($athleteIds->isNotEmpty()) {
            $performanceQuery->whereIn('user_id', $athleteIds);
        }

        $sportPerformance = $performanceQuery
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
                if ($athleteIds->isNotEmpty()) {
                    $q->whereIn('user_id', $athleteIds);
                }

                $q->orWhere(function ($q2) use ($orgId) {
                    $q2->where('type', 'narrative_summary')
                        ->where('organization_id', $orgId)
                        ->whereNull('user_id');
                });
            })
            ->orderByDesc('computed_at')
            ->limit(6)
            ->get();

        $riskyAthletes = $athleteIds->isEmpty()
            ? collect()
            : User::query()
                ->whereIn('id', $athleteIds)
                ->whereHas('profile', fn ($q) => $q->whereIn('injury_risk', ['high', 'medium']))
                ->with('profile')
                ->limit(6)
                ->get();

        return compact('kpi', 'athletes', 'recentEvents', 'chart', 'insights', 'riskyAthletes');
    }
}
