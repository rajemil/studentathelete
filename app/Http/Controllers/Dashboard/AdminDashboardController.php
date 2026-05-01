<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Insight;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TrainingRecommendation;
use App\Models\User;
use App\Services\Insights\InsightsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(InsightsService $insightsService): View
    {
        $now = CarbonImmutable::now();
        $orgId = auth()->user()->organization_id;

        $insightsService->ensureGenerated($now);

        $countsByRole = User::query()
            ->where('organization_id', $orgId)
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->all();

        $kpi = [
            'students' => (int) ($countsByRole['student'] ?? 0),
            'coaches' => (int) ($countsByRole['coach'] ?? 0),
            'admins' => (int) ($countsByRole['admin'] ?? 0),
            'sports' => Sport::query()->where('organization_id', $orgId)->count(),
            'teams' => Team::query()->where('organization_id', $orgId)->count(),
            'events_upcoming' => Event::query()
                ->whereNotNull('starts_at')
                ->where('starts_at', '>=', $now)
                ->where(function ($q) use ($orgId) {
                    $q->whereHas('team', fn ($t) => $t->where('organization_id', $orgId))
                        ->orWhereHas('sport', fn ($s) => $s->where('organization_id', $orgId));
                })
                ->count(),
        ];

        $sportsDistribution = Sport::query()
            ->where('organization_id', $orgId)
            ->withCount('students')
            ->orderByDesc('students_count')
            ->limit(8)
            ->get()
            ->map(fn (Sport $s) => ['label' => $s->name, 'value' => (int) $s->students_count])
            ->values();

        $performanceTrend = PerformanceScore::query()
            ->whereHas('sport', fn ($q) => $q->where('organization_id', $orgId))
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $now->subDays(30)->toDateString())
            ->orderBy('scored_on')
            ->get(['scored_on', 'score'])
            ->groupBy(fn ($row) => (string) $row->scored_on)
            ->map(fn ($rows) => round($rows->avg('score'), 2))
            ->take(30);

        $recentActivity = collect()
            ->merge(
                Event::query()
                    ->where(function ($q) use ($orgId) {
                        $q->whereHas('team', fn ($t) => $t->where('organization_id', $orgId))
                            ->orWhereHas('sport', fn ($s) => $s->where('organization_id', $orgId));
                    })
                    ->latest('created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn (Event $e) => [
                        'type' => 'Event',
                        'title' => $e->title,
                        'when' => $e->created_at,
                    ])
            )
            ->merge(
                TrainingRecommendation::query()
                    ->whereHas('student', fn ($q) => $q->where('organization_id', $orgId))
                    ->latest('created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn (TrainingRecommendation $t) => [
                        'type' => 'Recommendation',
                        'title' => $t->title,
                        'when' => $t->created_at,
                    ])
            )
            ->merge(
                PerformanceScore::query()
                    ->whereHas('sport', fn ($q) => $q->where('organization_id', $orgId))
                    ->latest('created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn (PerformanceScore $p) => [
                        'type' => 'Performance',
                        'title' => 'Score: '.$p->score.' ('.$p->category.')',
                        'when' => $p->created_at,
                    ])
            )
            ->sortByDesc('when')
            ->take(10)
            ->values();

        $chart = [
            'sportsDistribution' => [
                'labels' => $sportsDistribution->pluck('label'),
                'values' => $sportsDistribution->pluck('value'),
            ],
            'performanceTrend' => [
                'labels' => $performanceTrend->keys()->values(),
                'values' => $performanceTrend->values(),
            ],
        ];

        $insights = Insight::query()
            ->where(function ($q) use ($orgId) {
                $q->whereNull('user_id')
                    ->orWhereHas('user', fn ($u) => $u->where('organization_id', $orgId));
            })
            ->orderByDesc('computed_at')
            ->limit(6)
            ->get();

        $riskyAthletes = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->whereHas('profile', fn ($q) => $q->whereIn('injury_risk', ['high', 'medium']))
            ->with(['profile'])
            ->orderByRaw("case when (select injury_risk from profiles where profiles.user_id = users.id) = 'high' then 0 else 1 end")
            ->limit(6)
            ->get();

        return view('dashboards.admin', compact('kpi', 'chart', 'recentActivity', 'insights', 'riskyAthletes'));
    }
}
