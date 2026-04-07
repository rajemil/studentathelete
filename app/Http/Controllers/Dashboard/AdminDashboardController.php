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

        $insightsService->ensureGenerated($now);

        $countsByRole = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->all();

        $kpi = [
            'students' => (int) ($countsByRole['student'] ?? 0),
            'coaches' => (int) ($countsByRole['coach'] ?? 0),
            'admins' => (int) ($countsByRole['admin'] ?? 0),
            'sports' => Sport::query()->count(),
            'teams' => Team::query()->count(),
            'events_upcoming' => Event::query()->whereNotNull('starts_at')->where('starts_at', '>=', $now)->count(),
        ];

        $sportsDistribution = Sport::query()
            ->withCount('students')
            ->orderByDesc('students_count')
            ->limit(8)
            ->get()
            ->map(fn (Sport $s) => ['label' => $s->name, 'value' => (int) $s->students_count])
            ->values();

        $performanceTrend = PerformanceScore::query()
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $now->subDays(30)->toDateString())
            ->orderBy('scored_on')
            ->get(['scored_on', 'score'])
            ->groupBy(fn ($row) => (string) $row->scored_on)
            ->map(fn ($rows) => round($rows->avg('score'), 2))
            ->take(30);

        $recentActivity = collect()
            ->merge(Event::query()->latest('created_at')->limit(6)->get()->map(fn (Event $e) => [
                'type' => 'Event',
                'title' => $e->title,
                'when' => $e->created_at,
            ]))
            ->merge(TrainingRecommendation::query()->latest('created_at')->limit(6)->get()->map(fn (TrainingRecommendation $t) => [
                'type' => 'Recommendation',
                'title' => $t->title,
                'when' => $t->created_at,
            ]))
            ->merge(PerformanceScore::query()->latest('created_at')->limit(6)->get()->map(fn (PerformanceScore $p) => [
                'type' => 'Performance',
                'title' => 'Score: '.$p->score.' ('.$p->category.')',
                'when' => $p->created_at,
            ]))
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
            ->orderByDesc('computed_at')
            ->limit(6)
            ->get();

        $riskyAthletes = User::query()
            ->where('role', 'student')
            ->whereHas('profile', fn ($q) => $q->whereIn('injury_risk', ['high', 'medium']))
            ->with(['profile'])
            ->orderByRaw("case when (select injury_risk from profiles where profiles.user_id = users.id) = 'high' then 0 else 1 end")
            ->limit(6)
            ->get();

        return view('dashboards.admin', compact('kpi', 'chart', 'recentActivity', 'insights', 'riskyAthletes'));
    }
}

