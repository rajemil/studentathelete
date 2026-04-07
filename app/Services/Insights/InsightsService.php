<?php

namespace App\Services\Insights;

use App\Models\Insight;
use App\Models\PerformanceScore;
use App\Models\PlayerStat;
use App\Models\Sport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InsightsService
{
    public function ensureGenerated(CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        if (! Insight::query()->exists()) {
            return $this->generate($now);
        }

        return 0;
    }

    /**
     * Generate and persist a fresh batch of insights.
     * Safe to run repeatedly (uses hash_key upsert).
     */
    public function generate(CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        $insights = collect()
            ->merge($this->insightsPerformanceImproved($now))
            ->merge($this->insightsStaminaDecreasing($now))
            ->merge($this->insightsTopPerformersThisWeek($now))
            ->merge($this->insightsAtRiskAthletes($now));

        if ($insights->isEmpty()) {
            return 0;
        }

        // Upsert by hash_key
        Insight::query()->upsert(
            $insights->all(),
            ['hash_key'],
            ['user_id', 'sport_id', 'team_id', 'type', 'severity', 'title', 'message', 'payload', 'computed_at', 'updated_at']
        );

        return $insights->count();
    }

    /**
     * "Performance improved by X%" (week-over-week, per student per sport).
     */
    private function insightsPerformanceImproved(CarbonImmutable $now): Collection
    {
        $weekEnd = $now->startOfDay();
        $w1Start = $weekEnd->subDays(7);
        $w0Start = $weekEnd->subDays(14);

        $rows = PerformanceScore::query()
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $w0Start->toDateString())
            ->select(
                'user_id',
                'sport_id',
                DB::raw("avg(case when scored_on >= '{$w1Start->toDateString()}' then score end) as avg_w1"),
                DB::raw("avg(case when scored_on <  '{$w1Start->toDateString()}' then score end) as avg_w0"),
                DB::raw("count(case when scored_on >= '{$w1Start->toDateString()}' then 1 end) as n_w1"),
                DB::raw("count(case when scored_on <  '{$w1Start->toDateString()}' then 1 end) as n_w0")
            )
            ->groupBy('user_id', 'sport_id')
            ->get();

        return $rows->filter(function ($r) {
            return (int) $r->n_w1 >= 2 && (int) $r->n_w0 >= 2 && $r->avg_w0 !== null && (float) $r->avg_w0 > 0;
        })->map(function ($r) use ($now) {
            $avg0 = (float) $r->avg_w0;
            $avg1 = (float) $r->avg_w1;
            $pct = (($avg1 - $avg0) / $avg0) * 100.0;

            if ($pct < 8.0) {
                return null;
            }

            $user = User::find($r->user_id);
            $sport = $r->sport_id ? Sport::find($r->sport_id) : null;

            $title = 'Performance improved';
            $message = sprintf(
                '%s improved by %s%% vs last week%s.',
                $user?->name ?? 'Athlete',
                number_format($pct, 1),
                $sport ? ' in '.$sport->name : ''
            );

            return $this->row([
                'user_id' => $r->user_id,
                'sport_id' => $r->sport_id,
                'type' => 'performance_improved',
                'severity' => 'success',
                'title' => $title,
                'message' => $message,
                'payload' => [
                    'avg_previous_week' => round($avg0, 2),
                    'avg_current_week' => round($avg1, 2),
                    'pct_change' => round($pct, 2),
                ],
                'computed_at' => $now,
            ]);
        })->filter()->values();
    }

    /**
     * "Stamina decreasing" based on either:
     * - performance_scores category=stamina trend, OR
     * - player_stats.metrics->stamina trend.
     */
    private function insightsStaminaDecreasing(CarbonImmutable $now): Collection
    {
        $since = $now->subDays(21)->toDateString();

        $scoreStamina = PerformanceScore::query()
            ->where('category', 'stamina')
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $since)
            ->orderBy('scored_on')
            ->get(['user_id', 'sport_id', 'scored_on', 'score'])
            ->groupBy(fn ($r) => $r->user_id.'|'.($r->sport_id ?? 0));

        $ins = collect();

        foreach ($scoreStamina as $key => $rows) {
            $series = $rows->pluck('score')->map(fn ($v) => (float) $v)->values();
            if ($series->count() < 4) continue;

            $slope = $this->linearSlope($series);
            if ($slope > -0.20) continue;

            [$userId, $sportId] = explode('|', $key);
            $userId = (int) $userId;
            $sportId = (int) $sportId;

            $user = User::find($userId);
            $sport = $sportId ? Sport::find($sportId) : null;

            $ins->push($this->row([
                'user_id' => $userId,
                'sport_id' => $sportId ?: null,
                'type' => 'stamina_decreasing',
                'severity' => 'warning',
                'title' => 'Stamina decreasing',
                'message' => sprintf(
                    '%s shows a downward stamina trend%s. Consider recovery + base conditioning.',
                    $user?->name ?? 'Athlete',
                    $sport ? ' in '.$sport->name : ''
                ),
                'payload' => [
                    'slope_per_point' => round($slope, 4),
                    'points' => $series->count(),
                ],
                'computed_at' => $now,
            ]));
        }

        // player_stats.metrics stamina (optional)
        $statRows = PlayerStat::query()
            ->where('recorded_on', '>=', $since)
            ->whereNotNull('metrics')
            ->orderBy('recorded_on')
            ->get(['user_id', 'sport_id', 'recorded_on', 'metrics'])
            ->filter(fn ($r) => is_array($r->metrics) && array_key_exists('stamina', $r->metrics))
            ->groupBy(fn ($r) => $r->user_id.'|'.($r->sport_id ?? 0));

        foreach ($statRows as $key => $rows) {
            $series = $rows->map(fn ($r) => (float) ($r->metrics['stamina'] ?? 0))->values();
            if ($series->count() < 4) continue;
            $slope = $this->linearSlope($series);
            if ($slope > -0.20) continue;

            [$userId, $sportId] = explode('|', $key);
            $userId = (int) $userId;
            $sportId = (int) $sportId;

            $user = User::find($userId);
            $sport = $sportId ? Sport::find($sportId) : null;

            $ins->push($this->row([
                'user_id' => $userId,
                'sport_id' => $sportId ?: null,
                'type' => 'stamina_decreasing',
                'severity' => 'warning',
                'title' => 'Stamina decreasing',
                'message' => sprintf(
                    '%s stamina metrics are trending down%s. Consider adjusting load and recovery.',
                    $user?->name ?? 'Athlete',
                    $sport ? ' in '.$sport->name : ''
                ),
                'payload' => [
                    'source' => 'player_stats',
                    'slope_per_point' => round($slope, 4),
                    'points' => $series->count(),
                ],
                'computed_at' => $now,
            ]));
        }

        return $ins->values();
    }

    /**
     * "Top performer this week" per sport and overall.
     */
    private function insightsTopPerformersThisWeek(CarbonImmutable $now): Collection
    {
        $since = $now->subDays(7)->toDateString();

        $topBySport = PerformanceScore::query()
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $since)
            ->select('sport_id', 'user_id', DB::raw('avg(score) as avg_score'), DB::raw('count(*) as n'))
            ->groupBy('sport_id', 'user_id')
            ->having(DB::raw('count(*)'), '>=', 2)
            ->get()
            ->groupBy('sport_id')
            ->map(function ($rows) {
                return $rows->sortByDesc('avg_score')->first();
            })
            ->filter();

        $ins = collect();

        foreach ($topBySport as $sportId => $row) {
            $sport = $sportId ? Sport::find($sportId) : null;
            $user = User::find($row->user_id);
            $ins->push($this->row([
                'user_id' => $row->user_id,
                'sport_id' => $sportId ?: null,
                'type' => 'top_performer_week',
                'severity' => 'info',
                'title' => 'Top performer this week',
                'message' => sprintf(
                    '%s is the top performer this week%s (avg %s).',
                    $user?->name ?? 'Athlete',
                    $sport ? ' in '.$sport->name : '',
                    number_format((float) $row->avg_score, 1)
                ),
                'payload' => [
                    'avg_score' => round((float) $row->avg_score, 2),
                    'scores' => (int) $row->n,
                ],
                'computed_at' => $now,
            ]));
        }

        // Overall top performer
        $overall = PerformanceScore::query()
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $since)
            ->select('user_id', DB::raw('avg(score) as avg_score'), DB::raw('count(*) as n'))
            ->groupBy('user_id')
            ->having(DB::raw('count(*)'), '>=', 3)
            ->orderByDesc('avg_score')
            ->first();

        if ($overall) {
            $user = User::find($overall->user_id);
            $ins->push($this->row([
                'user_id' => $overall->user_id,
                'sport_id' => null,
                'type' => 'top_performer_week_overall',
                'severity' => 'info',
                'title' => 'Top performer this week (overall)',
                'message' => sprintf(
                    '%s leads overall this week (avg %s).',
                    $user?->name ?? 'Athlete',
                    number_format((float) $overall->avg_score, 1)
                ),
                'payload' => [
                    'avg_score' => round((float) $overall->avg_score, 2),
                    'scores' => (int) $overall->n,
                ],
                'computed_at' => $now,
            ]));
        }

        return $ins->values();
    }

    /**
     * "At-risk athlete (possible injury)" heuristic:
     * - Recent week avg drops by >= 15% vs previous week AND
     * - volatility is high OR recent 3 scores are strictly decreasing.
     */
    private function insightsAtRiskAthletes(CarbonImmutable $now): Collection
    {
        $weekEnd = $now->startOfDay();
        $w1Start = $weekEnd->subDays(7);
        $w0Start = $weekEnd->subDays(14);

        $agg = PerformanceScore::query()
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', $w0Start->toDateString())
            ->select(
                'user_id',
                'sport_id',
                DB::raw("avg(case when scored_on >= '{$w1Start->toDateString()}' then score end) as avg_w1"),
                DB::raw("avg(case when scored_on <  '{$w1Start->toDateString()}' then score end) as avg_w0"),
                DB::raw("count(case when scored_on >= '{$w1Start->toDateString()}' then 1 end) as n_w1"),
                DB::raw("count(case when scored_on <  '{$w1Start->toDateString()}' then 1 end) as n_w0")
            )
            ->groupBy('user_id', 'sport_id')
            ->get()
            ->filter(fn ($r) => (int) $r->n_w1 >= 3 && (int) $r->n_w0 >= 3 && $r->avg_w0 !== null && (float) $r->avg_w0 > 0);

        $ins = collect();

        foreach ($agg as $r) {
            $avg0 = (float) $r->avg_w0;
            $avg1 = (float) $r->avg_w1;
            $pct = (($avg1 - $avg0) / $avg0) * 100.0;

            if ($pct > -15.0) continue;

            $series = PerformanceScore::query()
                ->where('user_id', $r->user_id)
                ->when($r->sport_id, fn ($q) => $q->where('sport_id', $r->sport_id))
                ->whereNotNull('scored_on')
                ->orderByDesc('scored_on')
                ->limit(12)
                ->pluck('score')
                ->map(fn ($v) => (float) $v)
                ->values();

            $std = $this->stddev($series);
            $last3 = $series->take(3)->values();
            $decreasing3 = $last3->count() === 3 && $last3[0] > $last3[1] && $last3[1] > $last3[2];

            if (! ($std >= 10.0 || $decreasing3)) {
                continue;
            }

            $user = User::find($r->user_id);
            $sport = $r->sport_id ? Sport::find($r->sport_id) : null;

            $ins->push($this->row([
                'user_id' => $r->user_id,
                'sport_id' => $r->sport_id,
                'type' => 'at_risk_injury',
                'severity' => 'danger',
                'title' => 'At-risk athlete',
                'message' => sprintf(
                    '%s shows a significant performance drop (%s%%) with volatility%s. Consider rest and injury screening.',
                    $user?->name ?? 'Athlete',
                    number_format($pct, 1),
                    $sport ? ' in '.$sport->name : ''
                ),
                'payload' => [
                    'pct_change' => round($pct, 2),
                    'avg_previous_week' => round($avg0, 2),
                    'avg_current_week' => round($avg1, 2),
                    'stddev_recent' => round($std, 2),
                    'decreasing_last3' => $decreasing3,
                ],
                'computed_at' => $now,
            ]));
        }

        return $ins->values();
    }

    private function row(array $data): array
    {
        $hashKey = sha1(json_encode([
            'user_id' => $data['user_id'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
            'team_id' => $data['team_id'] ?? null,
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
        ]));

        return [
            'hash_key' => $hashKey,
            'user_id' => $data['user_id'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
            'team_id' => $data['team_id'] ?? null,
            'type' => $data['type'],
            'severity' => $data['severity'],
            'title' => $data['title'],
            'message' => $data['message'],
            'payload' => $data['payload'] ?? null,
            'computed_at' => $data['computed_at'],
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ];
    }

    private function linearSlope(Collection $series): float
    {
        $n = $series->count();
        if ($n < 2) return 0.0;

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
        if ($n < 2) return 0.0;
        $mean = (float) $series->avg();
        $var = $series->map(fn ($v) => ((float) $v - $mean) ** 2)->sum() / ($n - 1);
        return sqrt((float) $var);
    }
}

