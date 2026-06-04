<?php

namespace App\Actions\Performance;

use App\Jobs\GenerateAiInsightNarrative;
use App\Jobs\GenerateAiTrainingPlan;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use App\Notifications\NewScoreNotification;
use App\Notifications\PerformanceWarningNotification;
use App\Services\AI\AiManager;
use App\Services\InjuryRisk\InjuryRiskService;
use App\Services\Insights\InsightsService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;

class LogPerformanceAction
{
    protected InsightsService $insightsService;
    protected InjuryRiskService $injuryRiskService;

    public function __construct(InsightsService $insightsService, InjuryRiskService $injuryRiskService)
    {
        $this->insightsService = $insightsService;
        $this->injuryRiskService = $injuryRiskService;
    }

    /**
     * Log a new performance score.
     *
     * @param  User  $student
     * @param  Sport  $sport
     * @param  array  $data
     * @param  User  $actor
     * @return PerformanceScore
     */
    public function execute(User $student, Sport $sport, array $data, User $actor): PerformanceScore
    {
        $now = CarbonImmutable::now();

        $score = PerformanceScore::create([
            'user_id' => $student->id,
            'sport_id' => $sport->id,
            'team_id' => null,
            'category' => $data['category'],
            'score' => $data['score'],
            'scored_on' => $data['scored_on'],
            'breakdown' => [
                'entered_by' => $actor->id,
            ],
        ]);
        $score->load('sport');

        activity()
            ->performedOn($score)
            ->causedBy($actor)
            ->withProperties(['sport_id' => $sport->id, 'student_id' => $student->id])
            ->log('performance_score_created');

        $this->insightsService->generate($now);

        if ($student->profile) {
            $result = $this->injuryRiskService->computeForUser($student, $now);
            $student->profile->update([
                'fatigue_score' => $result['fatigue_score'],
                'injury_risk' => $result['injury_risk'],
            ]);

            if (in_array($result['injury_risk'], ['medium', 'high'], true)) {
                $student->notify(new PerformanceWarningNotification(
                    'Performance warning',
                    'Your fatigue score is '.$result['fatigue_score'].'/100 with '.$result['injury_risk'].' injury risk. Consider recovery.',
                    $result['inputs'] ?? []
                ));
            }
        }

        if ($student->profile) {
            GenerateAiTrainingPlan::dispatch(
                $student->id,
                $sport->id,
                true,
                (int) $student->organization_id,
            );
        }

        if (AiManager::isLlmAvailable()) {
            if (Cache::add('ai_insight_user_'.$student->id, true, 600)) {
                GenerateAiInsightNarrative::dispatch((int) $student->organization_id, $student->id);
            }
            if (Cache::add('ai_insight_org_'.$student->organization_id, true, 600)) {
                GenerateAiInsightNarrative::dispatch((int) $student->organization_id, null);
            }
        }

        $student->notify(new NewScoreNotification($score));

        return $score;
    }
}
