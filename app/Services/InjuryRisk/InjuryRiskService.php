<?php

namespace App\Services\InjuryRisk;

use App\Models\PerformanceScore;
use App\Models\PlayerStat;
use App\Models\Profile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class InjuryRiskService
{
    /**
     * Compute and persist fatigue/risk for all student profiles.
     */
    public function recomputeAll(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        $students = User::query()
            ->where('role', 'student')
            ->with('profile')
            ->get();

        $results = $this->computeForUsers($students, $now);
        $updated = 0;

        foreach ($students as $student) {
            if (! $student->profile) {
                continue;
            }

            $result = $results[(int) $student->id] ?? $this->computeForUser($student, $now);
            $student->profile->update([
                'fatigue_score' => $result['fatigue_score'],
                'injury_risk' => $result['injury_risk'],
            ]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Bulk fatigue/risk for many athletes (single query batch per metric).
     *
     * @param  Collection<int, User>  $athletes
     * @return array<int, array{fatigue_score: int, injury_risk: string, inputs?: array}>
     */
    public function computeForUsers(Collection $athletes, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        if ($athletes->isEmpty()) {
            return [];
        }

        $userIds = $athletes->pluck('id')->map(fn ($id) => (int) $id)->all();
        $since14 = $now->subDays(14)->toDateString();
        $w1Start = $now->subDays(7)->toDateString();
        $w0Start = $now->subDays(14)->toDateString();
        $w0End = CarbonImmutable::parse($w1Start)->subDay()->toDateString();

        $scoreCounts14 = PerformanceScore::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $since14)
            ->selectRaw('user_id, COUNT(*) as aggregate')
            ->groupBy('user_id')
            ->pluck('aggregate', 'user_id');

        $statCounts14 = PlayerStat::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('recorded_on')
            ->where('recorded_on', '>=', $since14)
            ->selectRaw('user_id, COUNT(*) as aggregate')
            ->groupBy('user_id')
            ->pluck('aggregate', 'user_id');

        $avgW1 = PerformanceScore::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $w1Start)
            ->selectRaw('user_id, AVG(score) as avg_score')
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id');

        $avgW0 = PerformanceScore::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('scored_on')
            ->whereBetween('scored_on', [$w0Start, $w0End])
            ->selectRaw('user_id, AVG(score) as avg_score')
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id');

        $profiles = Profile::query()
            ->whereIn('user_id', $userIds)
            ->get(['user_id', 'height_cm', 'weight_kg'])
            ->keyBy('user_id');

        $out = [];

        foreach ($athletes as $athlete) {
            $uid = (int) $athlete->id;
            $profile = $profiles->get($uid);
            $bmi = $profile?->bmi !== null ? (float) $profile->bmi : null;

            $activityCount14 = (int) ($scoreCounts14[$uid] ?? 0) + (int) ($statCounts14[$uid] ?? 0);
            $avgLast = (float) ($avgW1[$uid] ?? 0);
            $avgPrev = (float) ($avgW0[$uid] ?? 0);

            $dropPct = 0.0;
            if ($avgPrev > 0) {
                $dropPct = (($avgLast - $avgPrev) / $avgPrev) * 100.0;
            }

            $out[$uid] = $this->scoreFromInputs($bmi, $activityCount14, $avgLast, $avgPrev, $dropPct);
        }

        return $out;
    }

    /**
     * Compute fatigue and injury risk for a single athlete.
     *
     * Logic inputs:
     * - BMI
     * - Activity frequency (performance_scores + player_stats in last 14 days)
     * - Performance drops (week-over-week average)
     */
    public function computeForUser(User $athlete, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        /** @var Profile|null $profile */
        $profile = $athlete->profile;
        $bulk = $this->computeForUsers(collect([$athlete]), $now);

        return $bulk[(int) $athlete->id] ?? [
            'fatigue_score' => 0,
            'injury_risk' => 'low',
            'inputs' => [],
        ];
    }

    /**
     * @return array{fatigue_score: int, injury_risk: string, inputs: array<string, mixed>}
     */
    private function scoreFromInputs(?float $bmi, int $activityCount14, float $avgW1, float $avgW0, float $dropPct): array
    {
        $bmiRisk = 0.0;
        if ($bmi !== null) {
            if ($bmi < 18.5) {
                $bmiRisk = 18;
            } elseif ($bmi < 25) {
                $bmiRisk = 4;
            } elseif ($bmi < 30) {
                $bmiRisk = 14;
            } else {
                $bmiRisk = 24;
            }
        } else {
            $bmiRisk = 8;
        }

        $activityRisk = match (true) {
            $activityCount14 <= 1 => 10.0,
            $activityCount14 <= 3 => 6.0,
            $activityCount14 <= 10 => 10.0,
            $activityCount14 <= 16 => 20.0,
            default => 28.0,
        };

        $dropRisk = match (true) {
            $avgW0 === 0.0 && $avgW1 === 0.0 => 6.0,
            $dropPct <= -25.0 => 40.0,
            $dropPct <= -15.0 => 28.0,
            $dropPct <= -8.0 => 18.0,
            default => 6.0,
        };

        $fatigue = (int) round(max(0, min(100, $bmiRisk + $activityRisk + $dropRisk)));
        $risk = $fatigue >= 70 ? 'high' : ($fatigue >= 40 ? 'medium' : 'low');

        return [
            'fatigue_score' => $fatigue,
            'injury_risk' => $risk,
            'inputs' => [
                'bmi' => $bmi,
                'activity_count_14d' => $activityCount14,
                'avg_score_last_7d' => round($avgW1, 2),
                'avg_score_prev_7d' => round($avgW0, 2),
                'drop_pct' => round($dropPct, 2),
            ],
        ];
    }
}
