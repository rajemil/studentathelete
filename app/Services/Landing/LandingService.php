<?php

namespace App\Services\Landing;

use App\Models\Event;
use App\Models\InjuryRecord;
use App\Models\OrganizationSetting;
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
        return [
            'stats' => $this->getStatistics(),
            'activities' => $this->getActivityFeed(5),
            'events' => $this->getUpcomingEvents(3),
            'teamMembers' => $this->getTeamMembers(3),
            'insights' => $this->getPredictiveInsights(),
            'footer' => $this->getFooterSettings(),
        ];
    }

    protected function getStatistics(): array
    {
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
    }

    protected function getActivityFeed(int $limit = 5): array
    {
        $activities = collect();

        // Events
        Event::latest('created_at')->take($limit)->get()->each(function ($item) use ($activities) {
            $activities->push([
                'type' => 'event',
                'title' => "New event: {$item->title}",
                'date' => $item->created_at,
            ]);
        });

        // Scores
        PerformanceScore::latest('created_at')->take($limit)->get()->each(function ($item) use ($activities) {
            $activities->push([
                'type' => 'score',
                'title' => "New score recorded",
                'date' => $item->created_at,
            ]);
        });

        // Injuries
        InjuryRecord::latest('created_at')->take($limit)->get()->each(function ($item) use ($activities) {
            $activities->push([
                'type' => 'injury',
                'title' => "Injury logged",
                'date' => $item->created_at,
            ]);
        });

        return $activities->sortByDesc('date')->take($limit)->values()->toArray();
    }

    protected function getUpcomingEvents(int $limit = 3): array
    {
        return Event::where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->take($limit)
            ->get()
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
