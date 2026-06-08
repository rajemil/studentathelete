<?php

namespace App\Observers;

use App\Models\InjuryRecord;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Models\User;
use App\Services\Analytics\AnalyticsCache;

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

        // Bust the dashboard cache for every coach assigned to the same sport
        // as the affected athlete so roster/analytics changes appear immediately.
        $this->invalidateCoachDashboards($userId, $model);
    }

    private function invalidateCoachDashboards(int $athleteUserId, PerformanceScore|InjuryRecord|ParticipationLog $model): void
    {
        // Determine the sport IDs the athlete participates in.
        $sportIds = collect();

        if ($model instanceof PerformanceScore && $model->sport_id) {
            $sportIds->push((int) $model->sport_id);
        }

        // Also get all sports the athlete is enrolled in via the pivot.
        $athlete = User::query()->find($athleteUserId);
        if ($athlete) {
            $pivotSportIds = $athlete->sports()->pluck('sports.id')->map(fn ($v) => (int) $v);
            $sportIds = $sportIds->merge($pivotSportIds)->unique();
        }

        if ($sportIds->isEmpty()) {
            return;
        }

        // Find all coaches assigned to those sports and clear their dashboard caches.
        $coachIds = User::query()
            ->where('role', 'coach')
            ->whereIn('sport_id', $sportIds->all())
            ->pluck('id');

        foreach ($coachIds as $coachId) {
            AnalyticsCache::forgetUserDashboard((int) $coachId);
        }
    }
}
