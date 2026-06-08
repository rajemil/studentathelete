<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\User;
use App\Support\StaffNavContext;
use App\Services\Analytics\AnalyticsCache;
use App\Services\Team\TeamSuggestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SportTeamSuggestionController extends Controller
{
    public function __construct(
        private readonly TeamSuggestionService $teamSuggestions,
    ) {}

    public function index(Sport $sport): View
    {
        $this->authorize('view', $sport);

        return view('sports.team_suggestions', [
            'sport' => $sport,
            'result' => null,
        ]);
    }

    public function generate(Request $request, Sport $sport): View|RedirectResponse
    {
        $this->authorize('view', $sport);

        $validated = $request->validate([
            'mode' => ['required', 'in:strongest,balanced,compatibility'],
            'team_count' => ['required', 'integer', 'min:2', 'max:12'],
            'team_size' => ['required', 'integer', 'min:2', 'max:30'],
        ]);

        $studentIds = $sport->students()
            ->where('role', 'student')
            ->where('users.organization_id', $request->user()->organization_id)
            ->pluck('users.id');

        $students = User::query()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        if ($students->isEmpty()) {
            if (StaffNavContext::current() === StaffNavContext::PREDICTIVE) {
                return redirect()
                    ->route('staff.ai_recommendations.hub')
                    ->with('status', 'Assign student athletes to this sport first.');
            }

            return $this->redirectRoutePreservingModal($request, 'sports.show', $sport)
                ->with('status', 'Assign student athletes to this sport first.');
        }

        $mode = $validated['mode'];
        $teamCount = (int) $validated['team_count'];
        $teamSize = (int) $validated['team_size'];

        $cacheKey = AnalyticsCache::teamSuggestionsKey((int) $sport->id, $mode, $teamCount, $teamSize);

        $result = AnalyticsCache::remember(
            $cacheKey,
            fn () => $this->teamSuggestions->generate($students, $sport, $mode, $teamCount, $teamSize),
        );

        return view('sports.team_suggestions', [
            'sport' => $sport,
            'result' => $result,
        ]);
    }
}
