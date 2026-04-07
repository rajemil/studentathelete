<?php

namespace App\Http\Controllers;

use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use App\Notifications\NewScoreNotification;
use App\Notifications\PerformanceWarningNotification;
use App\Notifications\TrainingScheduleNotification;
use App\Services\InjuryRisk\InjuryRiskService;
use App\Services\Insights\InsightsService;
use App\Services\Training\TrainingRecommendationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceScoreController extends Controller
{
    public function index(Request $request, Sport $sport): View
    {
        $students = $sport->students()
            ->where('role', 'student')
            ->orderBy('name')
            ->get(['users.id', 'users.name', 'users.email']);

        $recent = PerformanceScore::query()
            ->with(['student:id,name', 'sport:id,name'])
            ->where('sport_id', $sport->id)
            ->orderByDesc('scored_on')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        $chart = PerformanceScore::query()
            ->where('sport_id', $sport->id)
            ->whereNotNull('scored_on')
            ->where('scored_on', '>=', CarbonImmutable::now()->subDays(30)->toDateString())
            ->orderBy('scored_on')
            ->get(['scored_on', 'score'])
            ->groupBy(fn ($row) => (string) $row->scored_on)
            ->map(fn ($rows) => round($rows->avg('score'), 2))
            ->take(30);

        return view('sports.scores', [
            'sport' => $sport,
            'students' => $students,
            'recent' => $recent,
            'chart' => [
                'labels' => $chart->keys()->values(),
                'values' => $chart->values(),
            ],
        ]);
    }

    public function store(Request $request, Sport $sport, InsightsService $insightsService, InjuryRiskService $injuryRisk, TrainingRecommendationService $training): RedirectResponse
    {
        $now = CarbonImmutable::now();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'category' => ['required', 'string', 'max:64'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scored_on' => ['required', 'date'],
        ]);

        $student = User::query()->where('role', 'student')->findOrFail($validated['user_id']);

        // Ensure student is assigned to the sport (auto-attach for convenience)
        $sport->students()->syncWithoutDetaching([$student->id]);

        $score = PerformanceScore::create([
            'user_id' => $student->id,
            'sport_id' => $sport->id,
            'team_id' => null,
            'category' => $validated['category'],
            'score' => $validated['score'],
            'scored_on' => $validated['scored_on'],
            'breakdown' => [
                'entered_by' => $request->user()->id,
            ],
        ]);
        $score->load('sport');

        // Refresh insights so dashboards update immediately after new data.
        $insightsService->generate($now);

        if ($student->profile) {
            $result = $injuryRisk->computeForUser($student, $now);
            $student->profile->update([
                'fatigue_score' => $result['fatigue_score'],
                'injury_risk' => $result['injury_risk'],
            ]);

            // Performance warning notification if risk escalates
            if (in_array($result['injury_risk'], ['medium', 'high'], true)) {
                $student->notify(new PerformanceWarningNotification(
                    'Performance warning',
                    'Your fatigue score is '.$result['fatigue_score'].'/100 with '.$result['injury_risk'].' injury risk. Consider recovery.',
                    $result['inputs'] ?? []
                ));
            }
        }

        if ($student->profile) {
            $plan = $training->generateWeeklyPlan($student, $sport, $now);
            $student->notify(new TrainingScheduleNotification($plan));
        }

        if ($score) {
            $student->notify(new NewScoreNotification($score));
        }

        return redirect()->route('sports.scores.index', $sport)
            ->with('status', 'Score saved.');
    }
}
