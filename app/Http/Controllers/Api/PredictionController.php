<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\User;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics)
    {
    }

    /**
     * GET /api/predictions/athletes/{user}?sport_id=&horizon_days=
     */
    public function athlete(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();

        // Students can view their own predictions; coaches/admins can view any.
        if (($actor->role ?? 'student') === 'student' && $actor->id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'horizon_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $sport = isset($validated['sport_id'])
            ? Sport::query()->find($validated['sport_id'])
            : null;

        $horizonDays = (int) ($validated['horizon_days'] ?? 14);

        $prediction = $this->analytics->predictAthletePerformance($user, $sport, $horizonDays);

        return response()->json([
            'athlete' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'sport' => $sport ? ['id' => $sport->id, 'name' => $sport->name] : null,
            'prediction' => $prediction,
        ]);
    }

    /**
     * GET /api/predictions/athletes/{user}/recommendations?sport_id=
     */
    public function recommendations(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (($actor->role ?? 'student') === 'student' && $actor->id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
        ]);

        $sport = isset($validated['sport_id'])
            ? Sport::query()->find($validated['sport_id'])
            : null;

        $bundle = $this->analytics->recommendations($user, $sport);

        return response()->json([
            'athlete' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'sport' => $sport ? ['id' => $sport->id, 'name' => $sport->name] : null,
            'recommendations' => $bundle,
        ]);
    }

    /**
     * POST /api/predictions/teams/win-probability
     * body: { sport_id?, team_a_user_ids:[], team_b_user_ids:[] }
     */
    public function teamWinProbability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'team_a_user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'team_a_user_ids.*' => ['integer', 'exists:users,id'],
            'team_b_user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'team_b_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $sport = isset($validated['sport_id'])
            ? Sport::query()->find($validated['sport_id'])
            : null;

        $teamA = User::query()->whereIn('id', $validated['team_a_user_ids'])->get(['id', 'name', 'role']);
        $teamB = User::query()->whereIn('id', $validated['team_b_user_ids'])->get(['id', 'name', 'role']);

        $teamAScore = $this->analytics->teamStrengthScore($teamA, $sport);
        $teamBScore = $this->analytics->teamStrengthScore($teamB, $sport);

        $pA = $this->analytics->winProbability($teamAScore, $teamBScore);
        $pB = round(100.0 - $pA, 1);

        return response()->json([
            'sport' => $sport ? ['id' => $sport->id, 'name' => $sport->name] : null,
            'team_a' => [
                'strength' => $teamAScore,
                'win_probability' => $pA,
                'members' => $teamA->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name]),
            ],
            'team_b' => [
                'strength' => $teamBScore,
                'win_probability' => $pB,
                'members' => $teamB->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name]),
            ],
        ]);
    }

    /**
     * POST /api/predictions/teams/strongest-lineup
     * body: { sport_id?, candidate_user_ids:[], lineup_size }
     */
    public function strongestLineup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'candidate_user_ids' => ['required', 'array', 'min:1', 'max:100'],
            'candidate_user_ids.*' => ['integer', 'exists:users,id'],
            'lineup_size' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $sport = isset($validated['sport_id'])
            ? Sport::query()->find($validated['sport_id'])
            : null;

        $candidates = User::query()
            ->whereIn('id', $validated['candidate_user_ids'])
            ->get(['id', 'name', 'role']);

        $result = $this->analytics->strongestLineup($candidates, $sport, (int) $validated['lineup_size']);

        return response()->json([
            'sport' => $sport ? ['id' => $sport->id, 'name' => $sport->name] : null,
            'lineup_size' => (int) $validated['lineup_size'],
            'lineup_strength' => $result['lineup_strength'],
            'lineup' => $result['lineup'],
        ]);
    }
}

