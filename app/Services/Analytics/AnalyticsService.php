<?php

namespace App\Services\Analytics;

use App\Models\Event;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Predict athlete performance for a sport using:
     * - recent weighted average
     * - linear trend (simple regression on time index)
     * - historical stability (variance penalty)
     */
    public function predictAthletePerformance(User $athlete, ?Sport $sport, int $horizonDays = 14): array
    {
        $horizonDays = max(1, min(90, $horizonDays));

        $scores = PerformanceScore::query()
            ->where('user_id', $athlete->id)
            ->when($sport, fn ($q) => $q->where('sport_id', $sport->id))
            ->whereNotNull('score')
            ->orderBy('scored_on')
            ->limit(200)
            ->get(['score', 'scored_on', 'category']);

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

        // Project slope forward modestly; scaling keeps predictions stable
        $slopeProjection = ($horizonDays / 14.0) * ($slope * 1.25);
        $pred = $recentWeightedAvg + $slopeProjection;
        $pred = $this->clamp($pred, 0, 100);

        // Confidence grows with sample size, shrinks with volatility
        $sizeFactor = min(1.0, $n / 20.0);
        $volPenalty = min(1.0, $std / 20.0); // std 20 => heavy penalty
        $confidence = round(max(0.05, $sizeFactor * (1.0 - $volPenalty)), 2);

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
            ],
        ];
    }

    /**
     * Team score: average of member predicted scores (or historical avg if available).
     */
    public function teamStrengthScore(Collection $athletes, ?Sport $sport): float
    {
        if ($athletes->isEmpty()) {
            return 0.0;
        }

        $scores = $athletes->map(function (User $u) use ($sport) {
            $pred = $this->predictAthletePerformance($u, $sport, 14);

            return (float) ($pred['predicted_score'] ?? 0);
        });

        return round((float) $scores->avg(), 2);
    }

    /**
     * Win probability using logistic curve on team strength difference.
     */
    public function winProbability(float $teamAScore, float $teamBScore): float
    {
        $diff = $teamAScore - $teamBScore;
        $scale = 6.0; // flatter => less extreme probabilities
        $p = 1.0 / (1.0 + exp(-$diff / $scale));

        return round($p * 100.0, 1);
    }

    /**
     * Suggest strongest lineup from candidates.
     */
    public function strongestLineup(Collection $candidates, ?Sport $sport, int $lineupSize): array
    {
        $lineupSize = max(1, min(30, $lineupSize));

        $ranked = $candidates->map(function (User $u) use ($sport) {
            $pred = $this->predictAthletePerformance($u, $sport, 14);

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
     * Recommendation bundle: training, strategy, and preparation date.
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

        // Simple prep window: 14 days before, adjusted if event is soon.
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
        // More recent points get higher weight: 1..n
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
        // x = 1..n, y = score
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
