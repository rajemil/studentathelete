<?php

namespace App\Services\Team;

use App\Models\Sport;
use App\Models\User;
use App\Services\Analytics\AthleteMetricsContext;
use App\Services\Analytics\PredictiveAnalyticsService;
use App\Services\InjuryRisk\InjuryRiskService;
use Illuminate\Support\Collection;

/**
 * Application-layer team recommendation orchestration.
 * Uses {@see PredictiveAnalyticsService} for projections; does not duplicate scoring logic.
 */
class TeamSuggestionService
{
    public function __construct(
        private readonly PredictiveAnalyticsService $analytics,
        private readonly InjuryRiskService $injuryRisk,
    ) {}

    /**
     * @param  Collection<int, User>  $students
     * @return array{
     *   mode: string,
     *   team_count: int,
     *   team_size: int,
     *   pool_count: int,
     *   teams: list<array>,
     *   win_probabilities?: list<list<float|null>>,
     *   compatibility?: array
     * }
     */
    public function generate(
        Collection $students,
        Sport $sport,
        string $mode,
        int $teamCount,
        int $teamSize,
    ): array {
        $teamCount = max(2, min(12, $teamCount));
        $teamSize = max(2, min(30, $teamSize));

        $metrics = AthleteMetricsContext::build($students, $sport, $this->injuryRisk);
        $pool = $this->buildPlayerPool($students, $sport, $metrics);

        if ($pool->isEmpty()) {
            return [
                'mode' => $mode,
                'team_count' => $teamCount,
                'team_size' => $teamSize,
                'pool_count' => 0,
                'teams' => [],
            ];
        }

        $teams = match ($mode) {
            'strongest' => $this->strongestTeams($pool, $teamCount, $teamSize, $sport),
            'compatibility' => $this->compatibilityTeams($pool, $teamCount, $teamSize, $sport),
            default => $this->balancedTeams($pool, $teamCount, $teamSize, $sport),
        };

        $winProbabilities = $this->pairwiseWinMatrix($teams, $sport);

        $result = [
            'mode' => $mode,
            'team_count' => $teamCount,
            'team_size' => $teamSize,
            'pool_count' => $pool->count(),
            'teams' => $teams,
            'win_probabilities' => $winProbabilities,
        ];

        if ($mode === 'compatibility') {
            $result['compatibility'] = $this->compatibilityAnalysis($pool, $teams);
        }

        return $result;
    }

    /**
     * @param  Collection<int, User>  $students
     * @return Collection<int, array<string, mixed>>
     */
    private function buildPlayerPool(Collection $students, Sport $sport, AthleteMetricsContext $metrics): Collection
    {
        return $students->map(function (User $u) use ($sport, $metrics) {
            $uid = (int) $u->id;
            $scores = $metrics->scoresFor($uid);
            $pred = $this->analytics->predictAthletePerformance($u, $sport, 14, $scores, $metrics);
            $risk = $metrics->risk($uid);
            $predicted = (float) ($pred['predicted_score'] ?? 0);
            $confidence = (float) ($pred['confidence'] ?? 0);
            $elo = 1000 + ($predicted * 10);

            return [
                'id' => $uid,
                'name' => $u->name,
                'email' => $u->email,
                'predicted_score' => round($predicted, 2),
                'score' => round($predicted, 2),
                'confidence' => $confidence,
                'trend' => $pred['trend'] ?? 'unknown',
                'elo_rating' => round($elo, 0),
                'fatigue_score' => (int) ($risk['fatigue_score'] ?? 0),
                'injury_risk' => $risk['injury_risk'] ?? 'low',
                'slope' => (float) ($pred['inputs']['slope_per_point'] ?? 0),
            ];
        })->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $pool
     * @return list<array<string, mixed>>
     */
    private function strongestTeams(Collection $pool, int $teamCount, int $teamSize, Sport $sport): array
    {
        $sorted = $pool->sortByDesc('predicted_score')->values();
        $chunks = $sorted->chunk($teamSize)->take($teamCount)->values();

        return $chunks->map(function (Collection $team, int $idx) use ($sport) {
            return $this->formatTeam('Strongest Team '.($idx + 1), $team, $sport, 'strongest');
        })->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $pool
     * @return list<array<string, mixed>>
     */
    private function balancedTeams(Collection $pool, int $teamCount, int $teamSize, Sport $sport): array
    {
        $sorted = $pool->sortByDesc('predicted_score')->values();
        $teams = collect(range(0, $teamCount - 1))->map(fn () => collect())->values();

        foreach ($sorted as $i => $player) {
            $round = (int) floor($i / $teamCount);
            $pos = $i % $teamCount;
            $index = $round % 2 === 0 ? $pos : ($teamCount - 1 - $pos);

            if ($teams[$index]->count() < $teamSize) {
                $teams[$index]->push($player);
            }
        }

        return $teams->map(function (Collection $team, int $idx) use ($sport) {
            return $this->formatTeam('Balanced Team '.($idx + 1), $team, $sport, 'balanced');
        })->all();
    }

    /**
     * Groups athletes with complementary trends (rising + stable) per team.
     *
     * @param  Collection<int, array<string, mixed>>  $pool
     * @return list<array<string, mixed>>
     */
    private function compatibilityTeams(Collection $pool, int $teamCount, int $teamSize, Sport $sport): array
    {
        $trendWeight = ['up' => 300, 'flat' => 200, 'unknown' => 150, 'down' => 100];
        $sorted = $pool->sortByDesc(
            fn ($p) => ($trendWeight[$p['trend'] ?? 'unknown'] ?? 100) + (float) $p['predicted_score']
        )->values();

        $teams = collect(range(0, $teamCount - 1))->map(fn () => collect())->values();

        foreach ($sorted as $i => $player) {
            $round = (int) floor($i / $teamCount);
            $pos = $i % $teamCount;
            $index = $round % 2 === 0 ? $pos : ($teamCount - 1 - $pos);

            if ($teams[$index]->count() < $teamSize) {
                $teams[$index]->push($player);
            }
        }

        return $teams
            ->filter(fn ($t) => $t->isNotEmpty())
            ->values()
            ->map(fn (Collection $team, int $idx) => $this->formatTeam('Compatible Team '.($idx + 1), $team, $sport, 'compatibility'))
            ->all();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $members
     * @return array<string, mixed>
     */
    private function formatTeam(string $name, Collection $members, Sport $sport, string $mode): array
    {
        $avgScore = round((float) $members->avg('predicted_score'), 2);
        $avgConfidence = round((float) $members->avg('confidence'), 2);
        $balance = $this->balanceScore($members);
        $users = User::query()->whereIn('id', $members->pluck('id'))->get(['id', 'name']);
        $strength = $this->analytics->teamStrengthScore($users, $sport);

        $explanation = match ($mode) {
            'strongest' => 'Data-driven recommendation: highest combined predictive performance scores, Elo-style ratings, and recent form. Injury risk and fatigue reduce projected output.',
            'balanced' => 'Statistical analysis: snake-draft distribution so each team has similar average predictive strength and balance score.',
            default => 'Team compatibility analysis: mixes performance trends (rising, stable, recovering) to reduce lineup volatility and spread injury-risk exposure.',
        };

        return [
            'name' => $name,
            'avg_score' => $avgScore,
            'team_strength' => $strength,
            'confidence_score' => round(min(0.95, $avgConfidence * 0.7 + $balance * 0.3), 2),
            'balance_score' => $balance,
            'explanation' => $explanation,
            'lineup' => $members->values()->all(),
            'members' => $members->map(function (array $m) {
                return array_merge($m, [
                    'explanation' => $this->memberExplanation($m),
                ]);
            })->values()->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $member
     */
    private function memberExplanation(array $member): string
    {
        $parts = [
            'Projected score '.$member['predicted_score'],
            'Elo '.$member['elo_rating'],
            'Trend: '.$member['trend'],
        ];

        if (($member['injury_risk'] ?? 'low') !== 'low') {
            $parts[] = 'Injury risk: '.$member['injury_risk'];
        }

        if (($member['fatigue_score'] ?? 0) >= 40) {
            $parts[] = 'Fatigue '.$member['fatigue_score'].'/100';
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $members
     */
    private function balanceScore(Collection $members): float
    {
        if ($members->count() < 2) {
            return 1.0;
        }

        $scores = $members->pluck('predicted_score')->map(fn ($v) => (float) $v);
        $spread = (float) $scores->max() - (float) $scores->min();

        return round(max(0.1, 1.0 - ($spread / 100.0)), 2);
    }

    /**
     * @param  list<array<string, mixed>>  $teams
     * @return list<list<float|null>>
     */
    private function pairwiseWinMatrix(array $teams, Sport $sport): array
    {
        $n = count($teams);
        $matrix = [];

        for ($i = 0; $i < $n; $i++) {
            $row = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i === $j) {
                    $row[] = null;
                    continue;
                }
                $strengthA = (float) ($teams[$i]['team_strength'] ?? $teams[$i]['avg_score'] ?? 0);
                $strengthB = (float) ($teams[$j]['team_strength'] ?? $teams[$j]['avg_score'] ?? 0);
                $row[] = $this->analytics->winProbability($strengthA, $strengthB);
            }
            $matrix[] = $row;
        }

        return $matrix;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $pool
     * @param  list<array<string, mixed>>  $teams
     * @return array<string, mixed>
     */
    private function compatibilityAnalysis(Collection $pool, array $teams): array
    {
        $pairs = [];
        $players = $pool->take(12)->values();

        for ($i = 0; $i < $players->count(); $i++) {
            for ($j = $i + 1; $j < $players->count(); $j++) {
                $a = $players[$i];
                $b = $players[$j];
                $score = $this->pairCompatibility($a, $b);
                $pairs[] = [
                    'athlete_a' => $a['name'],
                    'athlete_b' => $b['name'],
                    'compatibility' => round($score, 2),
                    'note' => $score >= 0.7
                        ? 'Complementary trends and similar readiness.'
                        : 'Mixed trends or elevated fatigue/injury signals.',
                ];
            }
        }

        return [
            'summary' => 'Team compatibility analysis uses performance trends, fatigue scores, and injury risk — not machine learning.',
            'top_pairs' => collect($pairs)->sortByDesc('compatibility')->take(8)->values()->all(),
            'teams_formed' => count($teams),
        ];
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     */
    private function pairCompatibility(array $a, array $b): float
    {
        $trendBonus = ($a['trend'] === 'up' && $b['trend'] === 'flat') || ($b['trend'] === 'up' && $a['trend'] === 'flat') ? 0.15 : 0;
        $riskPenalty = (($a['injury_risk'] ?? 'low') !== 'low' ? 0.1 : 0) + (($b['injury_risk'] ?? 'low') !== 'low' ? 0.1 : 0);
        $fatiguePenalty = min(0.2, (($a['fatigue_score'] + $b['fatigue_score']) / 200));

        $scoreGap = abs($a['predicted_score'] - $b['predicted_score']);
        $gapBonus = max(0, 0.2 - ($scoreGap / 100));

        return max(0.1, min(1.0, 0.55 + $trendBonus + $gapBonus - $riskPenalty - $fatiguePenalty));
    }
}
