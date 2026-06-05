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

class LandingService
{
    protected PredictiveAnalyticsService $predictiveAnalytics;
    protected InjuryRiskService $injuryRisk;

    public function __construct(
        PredictiveAnalyticsService $predictiveAnalytics,
        InjuryRiskService $injuryRisk
    ) {
        $this->predictiveAnalytics = $predictiveAnalytics;
        $this->injuryRisk = $injuryRisk;
    }

    public function getLandingData(): array
    {
        $insights = $this->getPredictiveInsights();

        return [
            'stats' => $this->getStatistics(),
            'activities' => $this->getActivityFeed(5),
            'events' => $this->getUpcomingEvents(3),
            'teams' => $this->getTeamMembers(3),
            'athletes' => $insights['topAthletes'] ?? [],
            // Keeping these to preserve UI bindings just in case, but aligning with requested schema
            'teamMembers' => $this->getTeamMembers(3),
            'insights' => $insights,
            'footer' => $this->getFooterSettings(),
        ];
    }

    protected function getStatistics(): array
    {
        $orgId = auth()->check() ? auth()->user()->organization_id : 'guest';
        $cacheKey = "landing_stats_{$orgId}";

        return cache()->remember($cacheKey, 60, function () {
            return [
                'students' => User::where('role', 'student')->count(),
                'coaches' => User::where('role', 'coach')->count(),
                'instructors' => User::where('role', 'instructor')->count(),
                'sports' => Sport::count(),
                'teams' => Team::count(),
                'events' => Event::count(),
                'scores' => PerformanceScore::count(),
                'injuries' => InjuryRecord::count(),
                'avgScore' => round(PerformanceScore::avg('score') ?? 0, 1),
            ];
        });
    }

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
                    'type' => 'performance',
                    'title' => 'Score recorded' . ($item->sport ? " in {$item->sport->name}" : '') . ": {$item->score}",
                    'date' => $item->scored_on ?? $item->created_at,
                    'user' => $item->student?->name ?? 'Unknown',
                ]);
            });

        // Participation Logs
        ParticipationLog::with('user', 'sport')
            ->latest('logged_on')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type' => 'participation',
                    'title' => ucfirst($item->activity_type ?? 'Activity') . ($item->sport ? " — {$item->sport->name}" : '') . " ({$item->duration_minutes} min)",
                    'date' => $item->logged_on ?? $item->created_at,
                    'user' => $item->user?->name ?? 'Unknown',
                ]);
            });

        // Injury Records
        InjuryRecord::with('athlete', 'sport')
            ->latest('occurred_on')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type' => 'injury',
                    'title' => "Injury reported: {$item->title}" . ($item->sport ? " ({$item->sport->name})" : ''),
                    'date' => $item->occurred_on ?? $item->created_at,
                    'user' => $item->athlete?->name ?? 'Unknown',
                ]);
            });

        // Events
        Event::latest('created_at')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type' => 'event',
                    'title' => "Event created: {$item->title}",
                    'date' => $item->created_at,
                    'user' => 'System',
                ]);
            });

        // Academic Records
        AcademicRecord::with('user')
            ->latest('created_at')
            ->take($limit)
            ->get()
            ->each(function ($item) use ($activities) {
                $activities->push([
                    'type' => 'academic',
                    'title' => "Academic record: {$item->semester} — GPA {$item->gpa}",
                    'date' => $item->created_at,
                    'user' => $item->user?->name ?? 'Unknown',
                ]);
            });

        return $activities->sortByDesc('date')->take($limit)->values()->toArray();
    }

    protected function getUpcomingEvents(int $limit = 3): array
    {
        return Event::with('sport')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->take($limit)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->title,
                    'date' => $event->starts_at,
                    'location' => $event->location,
                    'event_type' => $event->event_type,
                    'sport' => $event->sport?->name,
                ];
            })
            ->toArray();
    }

    protected function getTeamMembers(int $limit = 3): array
    {
        return TeamMember::take($limit)->get()->toArray();
    }

    protected function getPredictiveInsights(): array
    {
        $students = User::where('role', 'student')->with('performanceScores')->get();

        // 1. Top Athletes (by avg score)
        $topAthletes = $students->sortByDesc(function ($user) {
            return $user->performanceScores->avg('score');
        })->take(3)->map(function ($user) {
            return ['name' => $user->name, 'score' => round($user->performanceScores->avg('score') ?? 0, 1)];
        })->values()->toArray();

        // 2. Highest Injury Risk
        $riskData = $this->injuryRisk->computeForUsers($students);
        $highRiskAthletes = collect($riskData)->map(function ($data, $userId) use ($students) {
            $user = $students->firstWhere('id', $userId);
            return [
                'name' => $user ? $user->name : 'Unknown',
                'risk_score' => $data['risk_score'] ?? 0,
            ];
        })->sortByDesc('risk_score')->take(3)->values()->toArray();

        return [
            'topAthletes' => $topAthletes,
            'highRiskAthletes' => $highRiskAthletes,
        ];
    }

    protected function getFooterSettings(): ?array
    {
        $settings = OrganizationSetting::first();
        return $settings ? $settings->toArray() : null;
    }
}
