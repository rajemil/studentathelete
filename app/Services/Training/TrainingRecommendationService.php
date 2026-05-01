<?php

namespace App\Services\Training;

use App\Models\Event;
use App\Models\Sport;
use App\Models\TrainingRecommendation;
use App\Models\User;
use App\Services\Analytics\AnalyticsService;
use App\Services\InjuryRisk\InjuryRiskService;
use Carbon\CarbonImmutable;

class TrainingRecommendationService
{
    public function __construct(
        private readonly AnalyticsService $analytics,
        private readonly InjuryRiskService $injuryRisk,
    ) {}

    /**
     * Generate / upsert a weekly plan for a user (optionally sport-specific).
     * Returns the created/updated TrainingRecommendation.
     */
    public function generateWeeklyPlan(User $athlete, ?Sport $sport = null, ?CarbonImmutable $now = null): TrainingRecommendation
    {
        $now ??= CarbonImmutable::now();

        $weekStart = $now->startOfWeek(CarbonImmutable::MONDAY)->startOfDay();
        $weekEnd = $weekStart->addDays(6)->endOfDay();
        $weekKey = $weekStart->format('o-\\WW'); // ISO week, ex: 2026-W14

        $pred = $this->analytics->predictAthletePerformance($athlete, $sport, 14);
        $trend = (string) ($pred['trend'] ?? 'unknown');

        $risk = $this->injuryRisk->computeForUser($athlete, $now);
        $fatigue = (int) ($risk['fatigue_score'] ?? 0);
        $riskLevel = (string) ($risk['injury_risk'] ?? 'low');

        $nextEvent = $this->nextRelevantEvent($athlete, $sport, $now);
        $timeline = $this->buildPreparationTimeline($now, $nextEvent?->starts_at ? CarbonImmutable::parse($nextEvent->starts_at) : null, $fatigue);

        [$focus, $routine] = $this->trainingRoutine($trend, $fatigue, $nextEvent ? CarbonImmutable::parse($nextEvent->starts_at) : null, $now);
        $strategy = $this->gameStrategy($trend, $fatigue);

        $title = 'Weekly Plan ('.$weekKey.')'.($sport ? ' · '.$sport->name : '');
        $body = $this->renderPlanText($focus, $routine, $strategy, $timeline, $fatigue, $riskLevel, $nextEvent?->title);

        $hash = sha1(json_encode([
            'user_id' => $athlete->id,
            'sport_id' => $sport?->id,
            'week' => $weekKey,
        ]));

        $rec = TrainingRecommendation::query()
            ->where('user_id', $athlete->id)
            ->when($sport, fn ($q) => $q->where('sport_id', $sport->id), fn ($q) => $q->whereNull('sport_id'))
            ->where('status', 'active')
            ->where('metadata->week_key', $weekKey)
            ->first();

        if (! $rec) {
            $rec = new TrainingRecommendation;
            $rec->user_id = $athlete->id;
            $rec->sport_id = $sport?->id;
            $rec->created_by = null; // system-generated
            $rec->status = 'active';
        }

        $rec->title = $title;
        $rec->recommendation = $body;
        $rec->starts_on = $weekStart->toDateString();
        $rec->ends_on = $weekEnd->toDateString();
        $rec->metadata = [
            'hash' => $hash,
            'week_key' => $weekKey,
            'trend' => $trend,
            'fatigue_score' => $fatigue,
            'injury_risk' => $riskLevel,
            'prediction' => $pred,
            'next_event' => $nextEvent ? [
                'id' => $nextEvent->id,
                'title' => $nextEvent->title,
                'starts_at' => $nextEvent->starts_at,
            ] : null,
            'timeline' => $timeline,
        ];

        $rec->save();

        return $rec;
    }

    private function nextRelevantEvent(User $athlete, ?Sport $sport, CarbonImmutable $now): ?Event
    {
        return Event::query()
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
    }

    private function trainingRoutine(string $trend, int $fatigue, ?CarbonImmutable $eventAt, CarbonImmutable $now): array
    {
        $daysToEvent = $eventAt ? max(0, $now->diffInDays($eventAt, false)) : null;

        // Adjust intensity based on fatigue.
        $intensity = $fatigue >= 70 ? 'low' : ($fatigue >= 40 ? 'moderate' : 'high');

        $focus = match ($trend) {
            'up' => 'Consolidate gains and sharpen execution',
            'down' => 'Stabilize fundamentals and recover',
            default => 'Build consistency with balanced progression',
        };

        // If event soon, taper.
        if ($daysToEvent !== null && $daysToEvent <= 7) {
            $focus = 'Taper and prime for event performance';
            $intensity = $fatigue >= 40 ? 'low' : 'moderate';
        }

        $routine = match ($intensity) {
            'low' => [
                '2x technique session (light, high quality)',
                '2x mobility + recovery (20–30 min)',
                '1x easy aerobic base (zone 2)',
                'Sleep + hydration focus daily',
            ],
            'moderate' => [
                '2x sport-specific skills + tactical reps',
                '1x interval conditioning (controlled)',
                '1x strength foundation (moderate)',
                '1x mobility + recovery',
            ],
            default => [
                '2x high-intensity intervals (short)',
                '2x sport-specific skills + competitive reps',
                '1x strength/power session',
                '1x mobility + recovery',
            ],
        };

        return [$focus, $routine];
    }

    private function gameStrategy(string $trend, int $fatigue): array
    {
        $strategy = [];

        if ($fatigue >= 70) {
            $strategy[] = 'Prioritize efficiency: controlled starts and fewer high-risk bursts.';
            $strategy[] = 'Use substitutions/rotations earlier to preserve output.';
        } elseif ($fatigue >= 40) {
            $strategy[] = 'Maintain steady tempo early; ramp intensity after confidence checks.';
        } else {
            $strategy[] = 'Start assertive; leverage high-energy phases early.';
        }

        if ($trend === 'down') {
            $strategy[] = 'Simplify game plan: fundamentals first, reduce complexity.';
        } elseif ($trend === 'up') {
            $strategy[] = 'Lean into strengths: repeat high-performing patterns.';
        }

        return $strategy;
    }

    private function buildPreparationTimeline(CarbonImmutable $now, ?CarbonImmutable $eventAt, int $fatigue): array
    {
        if (! $eventAt) {
            return [
                'start_date' => $now->addDays(1)->toDateString(),
                'phases' => [
                    ['name' => 'Base', 'days' => 4, 'notes' => 'Aerobic + fundamentals'],
                    ['name' => 'Sharpen', 'days' => 2, 'notes' => 'Quality reps + tactics'],
                    ['name' => 'Recover', 'days' => 1, 'notes' => 'Mobility + sleep'],
                ],
            ];
        }

        $days = max(1, $now->diffInDays($eventAt, false));
        $start = $eventAt->subDays(min(14, $days))->toDateString();

        $recoverBias = $fatigue >= 70 ? 0.45 : ($fatigue >= 40 ? 0.33 : 0.22);
        $recoverDays = max(1, (int) round($days * $recoverBias));
        $sharpenDays = max(1, (int) round($days * 0.25));
        $baseDays = max(1, $days - $recoverDays - $sharpenDays);

        return [
            'start_date' => $start,
            'event_date' => $eventAt->toDateString(),
            'days_to_event' => $days,
            'phases' => [
                ['name' => 'Base', 'days' => $baseDays, 'notes' => 'Build capacity + fundamentals'],
                ['name' => 'Sharpen', 'days' => $sharpenDays, 'notes' => 'Sport-specific intensity + tactics'],
                ['name' => 'Taper/Recover', 'days' => $recoverDays, 'notes' => 'Reduce load, improve freshness'],
            ],
        ];
    }

    private function renderPlanText(string $focus, array $routine, array $strategy, array $timeline, int $fatigue, string $riskLevel, ?string $eventTitle): string
    {
        $lines = [];
        $lines[] = '## Focus';
        $lines[] = $focus;
        $lines[] = '';
        $lines[] = '## Readiness';
        $lines[] = "Fatigue score: {$fatigue}/100 · Injury risk: ".strtoupper($riskLevel).'.';
        $lines[] = '';
        $lines[] = '## Weekly routine';
        foreach ($routine as $r) {
            $lines[] = "- {$r}";
        }
        $lines[] = '';
        $lines[] = '## Game strategy';
        foreach ($strategy as $s) {
            $lines[] = "- {$s}";
        }
        $lines[] = '';
        $lines[] = '## Preparation timeline';
        if ($eventTitle) {
            $lines[] = "Target event: {$eventTitle}";
        }
        if (isset($timeline['start_date'])) {
            $lines[] = "Start: {$timeline['start_date']}".(isset($timeline['event_date']) ? " · Event: {$timeline['event_date']}" : '');
        }
        foreach (($timeline['phases'] ?? []) as $p) {
            $lines[] = "- {$p['name']}: {$p['days']} day(s) — {$p['notes']}";
        }

        return implode("\n", $lines);
    }
}
