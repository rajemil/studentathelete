<?php

namespace App\Services\Landing;

use App\Models\AcademicRecord;
use App\Models\Event;
use App\Models\InjuryRecord;
use App\Models\OrganizationSetting;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Analytics\PredictiveAnalyticsService;
use App\Services\InjuryRisk\InjuryRiskService;
use App\Services\Team\TeamSuggestionService;
use Carbon\CarbonImmutable;

class LandingService
{
    public function __construct(
        protected PredictiveAnalyticsService $predictiveAnalytics,
        protected InjuryRiskService $injuryRisk,
        protected TeamSuggestionService $teamSuggestion,
    ) {}

    /**
     * Single public method. Controllers call only this.
     */
    public function getLandingData(): array
    {
        $insights = $this->getPredictiveInsights();

        return [
            'stats'       => $this->getStatistics(),
            'activities'  => $this->getActivityFeed(10),
            'events'      => $this->getUpcomingEvents(3),
            'teams'       => $this->getTeamMembers(3),
            'athletes'    => $insights['topAthletes'] ?? [],
            'teamMembers' => $this->getTeamMembers(3),
            'insights'    => $insights,
            'footer'      => $this->getFooterSettings(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STATISTICS  (Phase 3 — tenant-scoped, cached 60s)
    // ─────────────────────────────────────────────────────────────────────────

    protected function getStatistics(): array
    {
        $orgId    = auth()->check() ? auth()->user()->organization_id : 'guest';
        $cacheKey = "landing_stats_{$orgId}";

        return cache()->remember($cacheKey, 60, function () {
            return [
                'students'    => User::where('role', 'student')->count(),
                'coaches'     => User::where('role', 'coach')->count(),
                'sports'      => Sport::count(),
                'teams'       => Team::count(),
                'events'      => Event::count(),
                'scores'      => PerformanceScore::count(),
                'injuries'    => InjuryRecord::count(),
                'avgScore'    => round(PerformanceScore::avg('score') ?? 0, 1),
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIVITY FEED  (Phase 4 — 5 sources, merged, latest first)
    // ─────────────────────────────────────────────────────────────────────────

    protected function getActivityFeed(int $limit = 10): array
    {
        $activities = collect();

        // Performance Scores
        PerformanceScore::with('student', 'sport')
            ->latest('scored_on')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type'  => 'performance',
                    'title' => 'Score recorded'
                        . ($item->sport ? " in {$item->sport->name}" : '')
                        . ": {$item->score}",
                    'date'  => $item->scored_on ?? $item->created_at,
                    'user'  => $item->student?->name ?? 'Unknown',
                ]);
            });

        // Participation Logs
        ParticipationLog::with('user', 'sport')
            ->latest('logged_on')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type'  => 'participation',
                    'title' => ucfirst($item->activity_type ?? 'Activity')
                        . ($item->sport ? " — {$item->sport->name}" : '')
                        . " ({$item->duration_minutes} min)",
                    'date'  => $item->logged_on ?? $item->created_at,
                    'user'  => $item->user?->name ?? 'Unknown',
                ]);
            });

        // Injury Records
        InjuryRecord::with('athlete', 'sport')
            ->latest('occurred_on')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type'  => 'injury',
                    'title' => "Injury reported: {$item->title}"
                        . ($item->sport ? " ({$item->sport->name})" : ''),
                    'date'  => $item->occurred_on ?? $item->created_at,
                    'user'  => $item->athlete?->name ?? 'Unknown',
                ]);
            });

        // Events
        Event::latest('created_at')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type'  => 'event',
                    'title' => "Event created: {$item->title}",
                    'date'  => $item->created_at,
                    'user'  => 'System',
                ]);
            });

        // Academic Records
        AcademicRecord::with('user')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type'  => 'academic',
                    'title' => "Academic record: {$item->semester} — GPA {$item->gpa}",
                    'date'  => $item->created_at,
                    'user'  => $item->user?->name ?? 'Unknown',
                ]);
            });

        return $activities->sortByDesc('date')->take($limit)->values()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPCOMING EVENTS  (Phase 5 — no expired, nearest first)
    // ─────────────────────────────────────────────────────────────────────────

    protected function getUpcomingEvents(int $limit = 3): array
    {
        return Event::with('sport')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->take($limit)
            ->get()
            ->map(fn ($event) => [
                'id'         => $event->id,
                'name'       => $event->title,
                'date'       => $event->starts_at,
                'location'   => $event->location,
                'event_type' => $event->event_type,
                'sport'      => $event->sport?->name,
            ])
            ->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEAM MEMBERS  (Phase 9 — Meet the Team section)
    // ─────────────────────────────────────────────────────────────────────────

    protected function getTeamMembers(int $limit = 3): array
    {
        return TeamMember::take($limit)->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PREDICTIVE HIGHLIGHTS  (Phase 6)
    // ─────────────────────────────────────────────────────────────────────────

    protected function getPredictiveInsights(): array
    {
        $students = User::where('role', 'student')
            ->with(['performanceScores', 'profile'])
            ->get();

        if ($students->isEmpty()) {
            return [
                'topAthletes'      => [],
                'mostImproved'     => [],
                'highRiskAthletes' => [],
                'strongestTeam'    => null,
            ];
        }

        return [
            'topAthletes'      => $this->rankTopAthletes($students),
            'mostImproved'     => $this->rankMostImproved($students),
            'highRiskAthletes' => $this->rankHighestInjuryRisk($students),
            'strongestTeam'    => $this->getStrongestTeam(),
        ];
    }

    /**
     * Top Athletes: ranked by average performance score DESC.
     */
    private function rankTopAthletes($students): array
    {
        return $students
            ->sortByDesc(fn ($u) => $u->performanceScores->avg('score') ?? 0)
            ->take(3)
            ->map(fn ($u) => [
                'name'  => $u->name,
                'score' => round($u->performanceScores->avg('score') ?? 0, 1),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Most Improved: biggest positive delta between avg score
     * in last 30 days vs prior 30 days.
     */
    private function rankMostImproved($students): array
    {
        $now      = CarbonImmutable::now();
        $mid      = $now->subDays(30)->toDateString();
        $start    = $now->subDays(60)->toDateString();

        $recent = PerformanceScore::query()
            ->whereIn('user_id', $students->pluck('id'))
            ->where('scored_on', '>=', $mid)
            ->selectRaw('user_id, AVG(score) as avg_score')
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id');

        $prior = PerformanceScore::query()
            ->whereIn('user_id', $students->pluck('id'))
            ->whereBetween('scored_on', [$start, $mid])
            ->selectRaw('user_id, AVG(score) as avg_score')
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id');

        return $students
            ->map(function ($u) use ($recent, $prior) {
                $r     = (float) ($recent[$u->id] ?? 0);
                $p     = (float) ($prior[$u->id] ?? 0);
                $delta = $p > 0 ? round($r - $p, 1) : 0;

                return ['name' => $u->name, 'improvement' => $delta];
            })
            ->filter(fn ($row) => $row['improvement'] > 0)
            ->sortByDesc('improvement')
            ->take(3)
            ->values()
            ->toArray();
    }

    /**
     * Highest Injury Risk: InjuryRiskService bulk computation, sorted by fatigue score.
     */
    private function rankHighestInjuryRisk($students): array
    {
        $riskData = $this->injuryRisk->computeForUsers($students);

        return collect($riskData)
            ->map(function ($data, $userId) use ($students) {
                $user = $students->firstWhere('id', $userId);
                return [
                    'name'          => $user?->name ?? 'Unknown',
                    'risk_level'    => $data['injury_risk']    ?? 'low',
                    'fatigue_score' => $data['fatigue_score']  ?? 0,
                ];
            })
            ->sortByDesc('fatigue_score')
            ->take(3)
            ->values()
            ->toArray();
    }

    /**
     * Strongest Team: evaluate every existing Team via teamStrengthScore,
     * return the one with the highest computed score.
     */
    private function getStrongestTeam(): ?array
    {
        $teams = Team::with(['students', 'sport'])->get();

        if ($teams->isEmpty()) {
            return null;
        }

        $best      = null;
        $bestScore = -1.0;

        foreach ($teams as $team) {
            $athletes = $team->students;
            if ($athletes->isEmpty()) {
                continue;
            }

            $score = $this->predictiveAnalytics->teamStrengthScore(
                $athletes,
                $team->sport
            );

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = [
                    'name'     => $team->name,
                    'sport'    => $team->sport?->name,
                    'strength' => $score,
                    'members'  => $athletes->pluck('name')->toArray(),
                ];
            }
        }

        return $best;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FOOTER
    // ─────────────────────────────────────────────────────────────────────────

    protected function getFooterSettings(): ?array
    {
        $orgId = auth()->check() ? auth()->user()->organization_id : OrganizationSetting::first()?->organization_id;

        if (!$orgId) {
            return null;
        }

        $settings = OrganizationSetting::where('organization_id', $orgId)->first();
        return $settings ? $settings->toArray() : null;
    }
}
