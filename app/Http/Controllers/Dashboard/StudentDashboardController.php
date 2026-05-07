<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiInsightNarrative;
use App\Jobs\GenerateAiTrainingPlan;
use App\Models\Event;
use App\Models\Insight;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\SportApplication;
use App\Models\TrainingRecommendation;
use App\Services\AI\AiManager;
use App\Services\InjuryRisk\InjuryRiskService;
use App\Services\Insights\InsightsService;
use App\Services\Training\TrainingRecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function __invoke(InsightsService $insightsService, InjuryRiskService $injuryRisk, TrainingRecommendationService $training): View
    {
        $user = auth()->user();
        $now = CarbonImmutable::now();
        $teamIds = $user->teams()->pluck('teams.id');
        $sportIds = $user->sports()->pluck('sports.id');

        $insightsService->ensureGenerated($now);

        if ($user->profile && ($user->profile->fatigue_score === null || $user->profile->injury_risk === null)) {
            $res = $injuryRisk->computeForUser($user, $now);
            $user->profile->update([
                'fatigue_score' => $res['fatigue_score'],
                'injury_risk' => $res['injury_risk'],
            ]);
        }

        $kpi = [
            'sports' => (int) $sportIds->count(),
            'teams' => (int) $teamIds->count(),
            'avg_score_30d' => (float) PerformanceScore::query()
                ->where('user_id', $user->id)
                ->whereNotNull('scored_on')
                ->where('scored_on', '>=', $now->subDays(30)->toDateString())
                ->avg('score') ?: 0,
        ];

        $recentScores = PerformanceScore::query()
            ->where('user_id', $user->id)
            ->orderByDesc('scored_on')
            ->limit(8)
            ->get();

        $scoreTrend = PerformanceScore::query()
            ->where('user_id', $user->id)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $now->subDays(30)->toDateString())
            ->orderBy('scored_on')
            ->get(['scored_on', 'score'])
            ->groupBy(fn ($row) => (string) $row->scored_on)
            ->map(fn ($rows) => round($rows->avg('score'), 2))
            ->take(30);

        $upcomingEvents = Event::query()
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', $now)
            ->where(function ($q) use ($user) {
                $q->whereHas('sport', fn ($s) => $s->where('organization_id', $user->organization_id))
                    ->orWhereHas('team', fn ($t) => $t->where('organization_id', $user->organization_id));
            })
            ->where(function ($q) use ($user, $teamIds, $sportIds) {
                $q->whereHas('participants', fn ($p) => $p->where('users.id', $user->id))
                    ->orWhereIn('team_id', $teamIds)
                    ->orWhereIn('sport_id', $sportIds);
            })
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $recommendations = TrainingRecommendation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($user->profile && $recommendations->isEmpty()) {
            $training->generateWeeklyPlanHeuristic($user, null, $now);
            if (AiManager::isLlmAvailable()) {
                GenerateAiTrainingPlan::dispatch($user->id, null, false, (int) $user->organization_id);
                if (Cache::add('ai_insight_user_'.$user->id, true, 600)) {
                    GenerateAiInsightNarrative::dispatch((int) $user->organization_id, $user->id);
                }
            }
            $recommendations = TrainingRecommendation::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        $chart = [
            'scoreTrend' => [
                'labels' => $scoreTrend->keys()->values(),
                'values' => $scoreTrend->values(),
            ],
        ];

        $insights = Insight::query()
            ->where('user_id', $user->id)
            ->orderByDesc('computed_at')
            ->limit(6)
            ->get();

        $risk = [
            'fatigue_score' => $user->profile?->fatigue_score,
            'injury_risk' => $user->profile?->injury_risk,
        ];

        $sportApply = [
            'org_sports_count' => (int) Sport::query()->where('organization_id', $user->organization_id)->count(),
            'pending_applications' => (int) SportApplication::query()
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->count(),
        ];

        return view('dashboards.student', compact('kpi', 'recentScores', 'upcomingEvents', 'recommendations', 'chart', 'insights', 'risk', 'sportApply'));
    }
}
