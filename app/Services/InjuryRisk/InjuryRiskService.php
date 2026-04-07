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
    public function recomputeAll(CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        $students = User::query()
            ->where('role', 'student')
            ->with('profile')
            ->get();

        $updated = 0;

        foreach ($students as $student) {
            if (! $student->profile) {
                continue;
            }

            $result = $this->computeForUser($student, $now);
            $student->profile->update([
                'fatigue_score' => $result['fatigue_score'],
                'injury_risk' => $result['injury_risk'],
            ]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Compute fatigue and injury risk for a single athlete.
     *
     * Logic inputs:
     * - BMI
     * - Activity frequency (performance_scores + player_stats in last 14 days)
     * - Performance drops (week-over-week average)
     */
    public function computeForUser(User $athlete, CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        /** @var Profile|null $profile */
        $profile = $athlete->profile;
        $bmi = $profile?->bmi !== null ? (float) $profile->bmi : null;

        // Activity: events in last 14 days via scores + player_stats records
        $since14 = $now->subDays(14)->toDateString();
        $scoreCount14 = (int) PerformanceScore::query()
            ->where('user_id', $athlete->id)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $since14)
            ->count();

        $statCount14 = (int) PlayerStat::query()
            ->where('user_id', $athlete->id)
            ->whereNotNull('recorded_on')
            ->where('recorded_on', '>=', $since14)
            ->count();

        $activityCount14 = $scoreCount14 + $statCount14;

        // Performance drop: compare last 7 days vs previous 7 days (overall scores)
        $w1Start = $now->subDays(7)->toDateString();
        $w0Start = $now->subDays(14)->toDateString();

        $avgW1 = (float) (PerformanceScore::query()
            ->where('user_id', $athlete->id)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $w1Start)
            ->avg('score') ?: 0);

        $avgW0 = (float) (PerformanceScore::query()
            ->where('user_id', $athlete->id)
            ->whereNotNull('scored_on')
            ->whereBetween('scored_on', [$w0Start, CarbonImmutable::parse($w1Start)->subDay()->toDateString()])
            ->avg('score') ?: 0);

        $dropPct = 0.0;
        if ($avgW0 > 0) {
            $dropPct = (($avgW1 - $avgW0) / $avgW0) * 100.0; // negative means drop
        }

        // --- scoring ---
        // BMI component: penalize underweight/overweight/obese (soft)
        $bmiRisk = 0.0;
        if ($bmi !== null) {
            if ($bmi < 18.5) $bmiRisk = 18;
            elseif ($bmi < 25) $bmiRisk = 4;
            elseif ($bmi < 30) $bmiRisk = 14;
            else $bmiRisk = 24;
        } else {
            $bmiRisk = 8; // unknown BMI
        }

        // Activity component: too much load increases fatigue; too little reduces confidence.
        // Target zone: 4–10 activity signals per 14 days.
        $activityRisk = 0.0;
        if ($activityCount14 <= 1) $activityRisk = 10;
        elseif ($activityCount14 <= 3) $activityRisk = 6;
        elseif ($activityCount14 <= 10) $activityRisk = 10;
        elseif ($activityCount14 <= 16) $activityRisk = 20;
        else $activityRisk = 28;

        // Performance drop component: drop >=15% is meaningful
        $dropRisk = 0.0;
        if ($avgW0 === 0.0 && $avgW1 === 0.0) {
            $dropRisk = 6; // no data
        } elseif ($dropPct <= -25.0) $dropRisk = 40;
        elseif ($dropPct <= -15.0) $dropRisk = 28;
        elseif ($dropPct <= -8.0) $dropRisk = 18;
        else $dropRisk = 6;

        // Fatigue score (0-100)
        $fatigue = $bmiRisk + $activityRisk + $dropRisk;
        $fatigue = (int) round(max(0, min(100, $fatigue)));

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

