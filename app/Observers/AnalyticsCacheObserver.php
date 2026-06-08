<?php

namespace App\Observers;

use App\Models\InjuryRecord;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Models\User;
use App\Services\Analytics\AnalyticsCache;
use App\Services\Sport\SportResolutionService;

class AnalyticsCacheObserver
{
    public function saved(PerformanceScore|InjuryRecord|ParticipationLog $model): void
    {
        $this->invalidate($model);
    }

    public function deleted(PerformanceScore|InjuryRecord|ParticipationLog $model): void
    {
        $this->invalidate($model);
    }

    private function invalidate(PerformanceScore|InjuryRecord|ParticipationLog $model): void
    {
        $userId = match (true) {
            $model instanceof PerformanceScore => (int) $model->user_id,
            $model instanceof ParticipationLog => (int) $model->user_id,
            $model instanceof InjuryRecord => (int) $model->athlete_user_id,
        };

        AnalyticsCache::forgetAthlete($userId);

        if ($model instanceof PerformanceScore && $model->sport_id) {
            AnalyticsCache::forgetSport((int) $model->sport_id);
        }

        $this->invalidateCoachDashboards($userId, $model);
    }

    private function invalidateCoachDashboards(int $athleteUserId, PerformanceScore|InjuryRecord|ParticipationLog $model): void
    {
        $sportIds = collect();

        if ($model instanceof PerformanceScore && $model->sport_id) {
            $sportIds->push((int) $model->sport_id);
        }

        if ($model instanceof ParticipationLog && $model->sport_id) {
            $sportIds->push((int) $model->sport_id);
        }

        if ($model instanceof InjuryRecord && $model->sport_id) {
            $sportIds->push((int) $model->sport_id);
        }

        $athlete = User::query()->find($athleteUserId);
        if ($athlete) {
            $sportIds = $sportIds
                ->merge(app(SportResolutionService::class)->athleteSportIds($athlete))
                ->unique()
                ->values();
        }

        if ($sportIds->isEmpty()) {
            return;
        }

        AnalyticsCache::forgetCoachDashboardsForSports(
            $sportIds,
            $athlete?->organization_id !== null ? (int) $athlete->organization_id : null,
        );
    }
}
