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
use App\Support\ScoreEntryRules;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PerformanceScoreController extends Controller
{
    public function index(Request $request, Sport $sport): View
    {
        $this->authorize('recordScores', $sport);

        $students = $sport->students()
            ->where('role', 'student')
            ->where('users.organization_id', $request->user()->organization_id)
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
        $this->authorize('recordScores', $sport);

        $now = CarbonImmutable::now();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'category' => ['required', 'string', 'max:64'],
            'score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scored_on' => ['required', 'date'],
        ]);

        $student = User::query()
            ->where('role', 'student')
            ->where('organization_id', $request->user()->organization_id)
            ->findOrFail($validated['user_id']);

        if (! ScoreEntryRules::requesterMayEnterScoreFor($request->user(), $student, $sport)) {
            throw ValidationException::withMessages([
                'user_id' => ['This athlete is not on your roster for this sport, or is not registered for the sport.'],
            ]);
        }

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

        activity()
            ->performedOn($score)
            ->causedBy($request->user())
            ->withProperties(['sport_id' => $sport->id, 'student_id' => $student->id])
            ->log('performance_score_created');

        $insightsService->generate($now);

        if ($student->profile) {
            $result = $injuryRisk->computeForUser($student, $now);
            $student->profile->update([
                'fatigue_score' => $result['fatigue_score'],
                'injury_risk' => $result['injury_risk'],
            ]);

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

        $student->notify(new NewScoreNotification($score));

        return $this->redirectRoutePreservingModal($request, 'sports.scores.index', $sport)
            ->with('status', 'Score saved.');
    }
}
