<?php

namespace App\Services\Analytics;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Cached facade over {@see PredictiveAnalyticsService} for HTTP/API consumers.
 *
 * Layering: PredictiveAnalyticsService (core engine) → AnalyticsService (cache) → controllers.
 */
class AnalyticsService
{
    public function __construct(
        private readonly PredictiveAnalyticsService $predictive,
    ) {}

    public function predictAthletePerformance(User $athlete, ?Sport $sport, int $horizonDays = 14): array
    {
        $sportId = $sport?->id;
        $horizonDays = max(1, min(90, $horizonDays));

        return AnalyticsCache::remember(
            AnalyticsCache::athletePredictionKey((int) $athlete->id, $sportId, $horizonDays),
            fn () => $this->predictive->predictAthletePerformance($athlete, $sport, $horizonDays),
        );
    }

    public function teamStrengthScore(Collection $athletes, ?Sport $sport): float
    {
        $memberHash = sha1($athletes->pluck('id')->sort()->implode(','));

        return AnalyticsCache::remember(
            AnalyticsCache::teamStrengthKey($memberHash, $sport?->id),
            fn () => $this->predictive->teamStrengthScore($athletes, $sport),
        );
    }

    public function winProbability(float $teamAScore, float $teamBScore): float
    {
        return AnalyticsCache::remember(
            AnalyticsCache::winProbabilityKey($teamAScore, $teamBScore),
            fn () => $this->predictive->winProbability($teamAScore, $teamBScore),
        );
    }

    /**
     * @return array{lineup: mixed, lineup_strength: float}
     */
    public function strongestLineup(Collection $candidates, ?Sport $sport, int $lineupSize): array
    {
        $memberHash = sha1($candidates->pluck('id')->sort()->implode(','));

        return AnalyticsCache::remember(
            AnalyticsCache::strongestLineupKey($memberHash, $sport?->id, $lineupSize),
            fn () => $this->predictive->strongestLineup($candidates, $sport, $lineupSize),
        );
    }

    public function recommendations(User $athlete, ?Sport $sport): array
    {
        return AnalyticsCache::remember(
            AnalyticsCache::athleteRecommendationsKey((int) $athlete->id, $sport?->id),
            fn () => $this->predictive->recommendations($athlete, $sport),
        );
    }
}
