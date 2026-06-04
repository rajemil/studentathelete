<?php

namespace App\Services\Analytics;

use App\Models\Event;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use App\Models\InjuryRecord;
use App\Models\ParticipationLog;
use App\Services\InjuryRisk\InjuryRiskService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Core predictive analytics engine (deterministic statistical models).
 * Not cached — use {@see AnalyticsService} for cached HTTP/API access.
 */
class PredictiveAnalyticsService
{
    public function __construct(
        private readonly InjuryRiskService $injuryRiskService
    ) {}

    /**
     * Predict athlete performance for a sport using:
     * - recent weighted average (base rating)
     * - linear trend (recent form)
     * - Athlete Fatigue Impact Model (fatigue status, injury history, recovery period)
     */
    public function predictAthletePerformance(
        User $athlete,
        ?Sport $sport,
        int $horizonDays = 14,
        ?Collection $preloadedScores = null,
        ?AthleteMetricsContext $metrics = null,
    ): array {
        $horizonDays = max(1, min(90, $horizonDays));

        if ($preloadedScores !== null) {
            $scores = $preloadedScores;
        } elseif ($metrics !== null) {
            $scores = $metrics->scoresFor((int) $athlete->id);
        } else {
            $scores = PerformanceScore::query()
                ->where('user_id', $athlete->id)
                ->when($sport, fn ($q) => $q->where('sport_id', $sport->id))
                ->whereNotNull('score')
                ->orderBy('scored_on')
                ->limit(200)
                ->get(['score', 'scored_on', 'category']);
        }

        $series = $scores
            ->filter(fn ($s) => $s->score !== null)
            ->values()
            ->map(fn ($s) => (float) $s->score);

        $n = $series->count();

        if ($n === 0) {
            return [
                'predicted_score' => null,
                'confidence' => 0.0,
                'trend' => 'unknown',
                'inputs' => [
                    'points' => 0,
                    'recent_avg' => null,
                    'slope_per_point' => null,
                ],
            ];
        }

        $recentWindow = min(10, $n);
        $recent = $series->slice(-$recentWindow)->values();

        $recentWeightedAvg = $this->weightedAverage($recent);
        $slope = $this->linearSlope($series);
        $std = $this->stddev($series);

        // Project slope forward modestly
        $slopeProjection = ($horizonDays / 14.0) * ($slope * 1.25);
        $baseScore = $recentWeightedAvg + $slopeProjection;
        $baseScore = $this->clamp($baseScore, 0, 100);

        $now = CarbonImmutable::now();
        $uid = (int) $athlete->id;

        if ($metrics !== null) {
            $riskData = $metrics->risk($uid);
            $activeInjuriesCount = $metrics->activeInjuries($uid);
            $lastInjury = $metrics->lastClearedInjury($uid);
            $lastActivityDate = $metrics->lastActivityDate($uid);
        } else {
            $riskData = $this->injuryRiskService->computeForUser($athlete, $now);
            $activeInjuriesCount = InjuryRecord::query()
                ->where('athlete_user_id', $uid)
                ->whereIn('status', ['open', 'monitoring'])
                ->count();
            $lastInjury = InjuryRecord::query()
                ->where('athlete_user_id', $uid)
                ->where('status', 'cleared')
                ->orderBy('occurred_on', 'desc')
                ->first();
            $lastScore = $scores->sortByDesc('scored_on')->first();
            $lastLog = ParticipationLog::query()
                ->where('user_id', $uid)
                ->orderBy('logged_on', 'desc')
                ->first();
            $lastActivityDate = null;
            if ($lastScore && $lastLog) {
                $lastActivityDate = max($lastScore->scored_on, $lastLog->logged_on);
            } elseif ($lastScore) {
                $lastActivityDate = $lastScore->scored_on;
            } elseif ($lastLog) {
                $lastActivityDate = $lastLog->logged_on;
            }
        }

        $fatigueScore = (int) ($riskData['fatigue_score'] ?? 0);
        $fatiguePenalty = min(0.25, $fatigueScore / 400.0);

        $injuryPenalty = 0.0;
        if ($activeInjuriesCount > 0) {
            $injuryPenalty = 0.30;
        } elseif ($lastInjury) {
            $daysSinceInjury = $now->diffInDays(CarbonImmutable::parse($lastInjury->occurred_on), false);
            if ($daysSinceInjury >= 0 && $daysSinceInjury < 14) {
                $injuryPenalty = 0.15 * (1.0 - ($daysSinceInjury / 14.0));
            }
        }

        $recoveryPenalty = 0.0;
        if ($lastActivityDate) {
            $daysSinceActivity = $now->diffInDays(CarbonImmutable::parse($lastActivityDate), false);
            if ($daysSinceActivity > 7) {
                // Rustiness penalty: 2% per day beyond 7 days, max 15%
                $recoveryPenalty = min(0.15, ($daysSinceActivity - 7) * 0.02);
            } elseif ($daysSinceActivity < 1) {
                // Fatigue penalty for back-to-back days
                $recoveryPenalty = 0.05;
            }
        }

        // Apply Fatigue Impact Model to Base Score
        $totalPenaltyFactor = 1.0 - ($fatiguePenalty + $injuryPenalty + $recoveryPenalty);
        $totalPenaltyFactor = max(0.40, $totalPenaltyFactor); // Ensure at least 40% performance is retained
        
        $pred = $baseScore * $totalPenaltyFactor;
        $pred = $this->clamp($pred, 0, 100);

        // Confidence calculation
        $sizeFactor = min(1.0, $n / 20.0);
        $volPenalty = min(1.0, $std / 20.0);
        $confidence = round(max(0.05, $sizeFactor * (1.0 - $volPenalty) * (1.0 - $fatiguePenalty)), 2);

        $trend = $slope > 0.15 ? 'up' : ($slope < -0.15 ? 'down' : 'flat');

        return [
            'predicted_score' => round($pred, 2),
            'confidence' => $confidence,
            'trend' => $trend,
            'inputs' => [
                'points' => $n,
                'recent_avg' => round($recentWeightedAvg, 2),
                'slope_per_point' => round($slope, 4),
                'stddev' => round($std, 2),
                'horizon_days' => $horizonDays,
                'fatigue_score' => $fatigueScore,
                'active_injuries' => $activeInjuriesCount,
                'fatigue_penalty' => round($fatiguePenalty * 100, 1) . '%',
                'injury_penalty' => round($injuryPenalty * 100, 1) . '%',
                'recovery_penalty' => round($recoveryPenalty * 100, 1) . '%',
            ],
        ];
    }

    /**
     * Team Strength Score: Incorporating individual predicted scores, active injuries, roster depth, and recent form
     */
    public function teamStrengthScore(Collection $athletes, ?Sport $sport, ?AthleteMetricsContext $metrics = null): float
    {
        if ($athletes->isEmpty()) {
            return 0.0;
        }

        $metrics ??= AthleteMetricsContext::build($athletes, $sport, $this->injuryRiskService);

        $scores = $athletes->map(function (User $u) use ($sport, $metrics) {
            $userScores = $metrics->scoresFor((int) $u->id);
            $pred = $this->predictAthletePerformance($u, $sport, 14, $userScores, $metrics);
            return [
                'score' => (float) ($pred['predicted_score'] ?? 0),
                'trend' => $pred['trend'] ?? 'flat',
                'slope' => (float) ($pred['inputs']['slope_per_point'] ?? 0),
            ];
        });

        $avgScore = $scores->avg('score');

        $activeInjuriesCount = $athletes->sum(fn (User $u) => $metrics->activeInjuries((int) $u->id));

        // 1. Injury Penalty Factor (5% penalty per active injury, max 25%)
        $injuryPenaltyFactor = max(0.75, 1.0 - ($activeInjuriesCount * 0.05));

        // 2. Roster Depth Adjustment: Optimal team depth is 5+ athletes. 
        // Small rosters (< 5) get a small penalty. Large rosters get a small depth bonus.
        $rosterSize = $athletes->count();
        $depthFactor = 1.0;
        if ($rosterSize < 5) {
            $depthFactor = 0.90 + ($rosterSize * 0.02); // 1 => 0.92, 4 => 0.98
        } else {
            $depthFactor = min(1.05, 1.0 + (($rosterSize - 5) * 0.005)); // max 1.05
        }

        // 3. Recent Form Bonus: Based on trend slopes
        $avgSlope = $scores->avg('slope');
        $formBonus = $avgSlope * 2.0; // scales form trend directly

        $teamStrength = ($avgScore * $injuryPenaltyFactor * $depthFactor) + $formBonus;
        
        return round($this->clamp($teamStrength, 0, 100), 2);
    }

    /**
     * Elo-based Team Rating System Formula:
     * P(A) = 1 / (1 + 10^((RatingB - RatingA)/400))
     */
    public function winProbability(float $teamAScore, float $teamBScore): float
    {
        // Rescale the team strength scores to typical Elo ratings (e.g. 1000 to 2000)
        // Let's map a 0-100 strength score to a 1000-2000 Elo scale: Elo = 1000 + (Score * 10)
        $ratingA = 1000 + ($teamAScore * 10);
        $ratingB = 1000 + ($teamBScore * 10);

        $exponent = ($ratingB - $ratingA) / 400.0;
        $probA = 1.0 / (1.0 + pow(10, $exponent));

        return round($probA * 100.0, 1);
    }

    /**
     * Suggest strongest lineup from candidates.
     */
    public function strongestLineup(Collection $candidates, ?Sport $sport, int $lineupSize): array
    {
        $lineupSize = max(1, min(30, $lineupSize));

        $metrics = AthleteMetricsContext::build($candidates, $sport, $this->injuryRiskService);

        $ranked = $candidates->map(function (User $u) use ($sport, $metrics) {
            $userScores = $metrics->scoresFor((int) $u->id);
            $pred = $this->predictAthletePerformance($u, $sport, 14, $userScores, $metrics);

            return [
                'id' => $u->id,
                'name' => $u->name,
                'predicted_score' => $pred['predicted_score'],
                'confidence' => $pred['confidence'],
                'trend' => $pred['trend'],
            ];
        })->sortBy([
            ['predicted_score', 'desc'],
            ['confidence', 'desc'],
            ['name', 'asc'],
        ])->values();

        $lineup = $ranked->take($lineupSize)->values();

        return [
            'lineup' => $lineup,
            'lineup_strength' => round((float) $lineup->avg('predicted_score'), 2),
        ];
    }

    /**
     * Recommendation bundle based on predictions.
     */
    public function recommendations(User $athlete, ?Sport $sport): array
    {
        $pred = $this->predictAthletePerformance($athlete, $sport, 14);
        $trend = (string) ($pred['trend'] ?? 'unknown');
        $confidence = (float) ($pred['confidence'] ?? 0);

        $training = match ($trend) {
            'up' => [
                'focus' => 'Performance consolidation',
                'routine' => [
                    '2x/week high-intensity intervals (short)',
                    '2x/week sport-specific skill session',
                    '1-2x/week mobility + recovery',
                ],
            ],
            'down' => [
                'focus' => 'Stability + recovery + fundamentals',
                'routine' => [
                    '1x/week deload/recovery session',
                    '2x/week technique + low-impact conditioning',
                    '1-2x/week strength foundation (moderate)',
                ],
            ],
            default => [
                'focus' => 'Balanced progression',
                'routine' => [
                    '2x/week mixed conditioning',
                    '2x/week technique/sport-specific drills',
                    '1x/week strength or plyometrics (light)',
                ],
            ],
        };

        $strategy = [
            'suggestions' => array_values(array_filter([
                $trend === 'up' ? 'Start aggressive; maintain tempo early.' : null,
                $trend === 'down' ? 'Prioritize efficiency; avoid early burnout.' : null,
                $confidence < 0.35 ? 'Collect more scoring data to improve prediction quality.' : null,
            ])),
        ];

        $now = CarbonImmutable::now();
        $prep = $this->bestPreparationDate($athlete, $sport, $now);

        return [
            'training' => $training,
            'strategy' => $strategy,
            'best_preparation_date' => $prep['best_preparation_date'],
            'rationale' => $prep['rationale'],
            'prediction' => $pred,
        ];
    }

    private function bestPreparationDate(User $athlete, ?Sport $sport, CarbonImmutable $now): array
    {
        $nextEvent = Event::query()
            ->whereNotNull('starts_at')
            ->where('starts_at', '>=', $now)
            ->when($sport, fn ($q) => $q->where('sport_id', $sport->id))
            ->where(function ($q) use ($athlete) {
                $q->whereHas('participants', fn ($p) => $p->where('users.id', $athlete->id))
                    ->orWhereIn('team_id', $athlete->teams()->pluck('teams.id'))
                    ->orWhereIn('sport_id', $athlete->sports()->pluck('sports.id'));
            })
            ->orderBy('starts_at')
            ->first();

        if (! $nextEvent) {
            return [
                'best_preparation_date' => $now->addDays(7)->toDateString(),
                'rationale' => 'No upcoming event found; starting within 7 days maintains momentum.',
            ];
        }

        $eventDate = CarbonImmutable::parse($nextEvent->starts_at);
        $start = $eventDate->subDays(14);
        if ($start < $now) {
            $start = $now->addDays(1);
        }

        return [
            'best_preparation_date' => $start->toDateString(),
            'rationale' => 'Preparation starts 14 days before the next event ("'.$nextEvent->title.'").',
        ];
    }

    private function weightedAverage(Collection $values): float
    {
        $n = $values->count();
        if ($n === 0) {
            return 0.0;
        }
        $weights = collect(range(1, $n));
        $sumW = (float) $weights->sum();
        $sum = 0.0;
        foreach ($values as $i => $v) {
            $sum += (float) $v * ((float) $weights[$i] / $sumW);
        }

        return $sum;
    }

    private function linearSlope(Collection $series): float
    {
        $n = $series->count();
        if ($n < 2) {
            return 0.0;
        }

        $xs = collect(range(1, $n));
        $xMean = (float) $xs->avg();
        $yMean = (float) $series->avg();

        $num = 0.0;
        $den = 0.0;
        foreach ($series as $i => $y) {
            $x = (float) $xs[$i];
            $num += ($x - $xMean) * ((float) $y - $yMean);
            $den += ($x - $xMean) ** 2;
        }

        return $den == 0.0 ? 0.0 : ($num / $den);
    }

    private function stddev(Collection $series): float
    {
        $n = $series->count();
        if ($n < 2) {
            return 0.0;
        }
        $mean = (float) $series->avg();
        $var = $series->map(fn ($v) => ((float) $v - $mean) ** 2)->sum() / ($n - 1);

        return sqrt((float) $var);
    }

    private function clamp(float $v, float $min, float $max): float
    {
        return max($min, min($max, $v));
    }
}
