<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;

final class AnalyticsCache
{
    public const TTL_SECONDS = 3600;

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? self::TTL_SECONDS, $callback);
    }

    public static function forget(string $key): void
    {
        Cache::forget($key);
    }

    public static function athletePredictionKey(int $userId, ?int $sportId, int $horizonDays): string
    {
        return sprintf('analytics:athlete:%d:%s:%d', $userId, $sportId ?? 'all', $horizonDays);
    }

    public static function athleteRecommendationsKey(int $userId, ?int $sportId): string
    {
        return sprintf('analytics:recommendations:%d:%s', $userId, $sportId ?? 'all');
    }

    public static function teamStrengthKey(string $memberHash, ?int $sportId): string
    {
        return sprintf('analytics:team_strength:%s:%s', $memberHash, $sportId ?? 'all');
    }

    public static function winProbabilityKey(float $teamA, float $teamB): string
    {
        return sprintf('analytics:win_prob:%.2f:%.2f', $teamA, $teamB);
    }

    public static function strongestLineupKey(string $memberHash, ?int $sportId, int $lineupSize): string
    {
        return sprintf('analytics:lineup:%s:%s:%d', $memberHash, $sportId ?? 'all', $lineupSize);
    }

    public static function teamSuggestionsKey(int $sportId, string $mode, int $teamCount, int $teamSize): string
    {
        return sprintf('analytics:team_suggestions:%d:%s:%d:%d', $sportId, $mode, $teamCount, $teamSize);
    }

    public static function dashboardPayloadKey(int $userId): string
    {
        return sprintf('analytics:dashboard:%d', $userId);
    }

    public static function forgetAthlete(int $userId): void
    {
        // Pattern-free drivers: forget known key shapes used in this app.
        foreach ([7, 14, 30] as $horizon) {
            self::forget(self::athletePredictionKey($userId, null, $horizon));
            self::forget(self::athleteRecommendationsKey($userId, null));
        }
    }

    public static function forgetSport(int $sportId): void
    {
        foreach (['strongest', 'balanced', 'compatibility'] as $mode) {
            foreach ([2, 3, 4] as $teams) {
                foreach ([5, 6, 7, 8] as $size) {
                    self::forget(self::teamSuggestionsKey($sportId, $mode, $teams, $size));
                }
            }
        }
    }

    public static function forgetUserDashboard(int $userId): void
    {
        self::forget(self::dashboardPayloadKey($userId));
    }
}
