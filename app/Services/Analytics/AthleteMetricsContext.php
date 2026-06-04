<?php

namespace App\Services\Analytics;

use App\Models\InjuryRecord;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use App\Services\InjuryRisk\InjuryRiskService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Bulk-loaded metrics for predictive analytics (avoids N+1 per athlete).
 */
final class AthleteMetricsContext
{
    /**
     * @param  Collection<int, Collection<int, PerformanceScore>>  $scoresByUser
     * @param  array<int, int>  $activeInjuryCounts
     * @param  array<int, InjuryRecord|null>  $lastClearedInjury
     * @param  array<int, string|null>  $lastActivityDates
     * @param  array<int, array{fatigue_score: int, injury_risk: string}>  $riskByUser
     */
    public function __construct(
        public readonly Collection $scoresByUser,
        public readonly array $activeInjuryCounts,
        public readonly array $lastClearedInjury,
        public readonly array $lastActivityDates,
        public readonly array $riskByUser,
    ) {}

    /**
     * @param  Collection<int, User>  $athletes
     */
    public static function build(Collection $athletes, ?Sport $sport, InjuryRiskService $injuryRisk): self
    {
        if ($athletes->isEmpty()) {
            return new self(collect(), [], [], [], []);
        }

        $userIds = $athletes->pluck('id')->map(fn ($id) => (int) $id)->all();
        $now = CarbonImmutable::now();

        $scoresQuery = PerformanceScore::query()
            ->whereIn('user_id', $userIds)
            ->when($sport, fn ($q) => $q->where('sport_id', $sport->id))
            ->whereNotNull('score')
            ->orderBy('scored_on')
            ->limit(200 * max(1, count($userIds)));

        $scoresByUser = $scoresQuery
            ->get(['score', 'scored_on', 'category', 'user_id'])
            ->groupBy('user_id');

        $activeInjuryCounts = InjuryRecord::query()
            ->whereIn('athlete_user_id', $userIds)
            ->whereIn('status', ['open', 'monitoring'])
            ->selectRaw('athlete_user_id, COUNT(*) as aggregate')
            ->groupBy('athlete_user_id')
            ->pluck('aggregate', 'athlete_user_id')
            ->map(fn ($c) => (int) $c)
            ->all();

        $lastCleared = InjuryRecord::query()
            ->whereIn('athlete_user_id', $userIds)
            ->where('status', 'cleared')
            ->orderByDesc('occurred_on')
            ->get()
            ->unique('athlete_user_id')
            ->keyBy('athlete_user_id');

        $lastScoreDates = PerformanceScore::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('scored_on')
            ->selectRaw('user_id, MAX(scored_on) as last_scored')
            ->groupBy('user_id')
            ->pluck('last_scored', 'user_id');

        $lastLogDates = ParticipationLog::query()
            ->whereIn('user_id', $userIds)
            ->selectRaw('user_id, MAX(logged_on) as last_logged')
            ->groupBy('user_id')
            ->pluck('last_logged', 'user_id');

        $lastActivityDates = [];
        foreach ($userIds as $uid) {
            $scoreDate = $lastScoreDates[$uid] ?? null;
            $logDate = $lastLogDates[$uid] ?? null;
            if ($scoreDate && $logDate) {
                $lastActivityDates[$uid] = (string) max($scoreDate, $logDate);
            } else {
                $lastActivityDates[$uid] = $scoreDate ? (string) $scoreDate : ($logDate ? (string) $logDate : null);
            }
        }

        $riskByUser = $injuryRisk->computeForUsers($athletes, $now);

        return new self(
            $scoresByUser,
            $activeInjuryCounts,
            $lastCleared->all(),
            $lastActivityDates,
            $riskByUser,
        );
    }

    public function scoresFor(int $userId): Collection
    {
        return $this->scoresByUser->get($userId, collect());
    }

    public function activeInjuries(int $userId): int
    {
        return $this->activeInjuryCounts[$userId] ?? 0;
    }

    public function lastClearedInjury(int $userId): ?InjuryRecord
    {
        return $this->lastClearedInjury[$userId] ?? null;
    }

    public function lastActivityDate(int $userId): ?string
    {
        return $this->lastActivityDates[$userId] ?? null;
    }

    /**
     * @return array{fatigue_score: int, injury_risk: string}
     */
    public function risk(int $userId): array
    {
        return $this->riskByUser[$userId] ?? ['fatigue_score' => 0, 'injury_risk' => 'low'];
    }
}
