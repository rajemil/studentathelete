<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\User;
use App\Services\Analytics\PredictiveAnalyticsService;
use App\Services\Sport\SportResolutionService;
use App\Support\CoachedTeams;
use App\Support\RosterAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Thesis-aligned predictive analytics HTTP entry points.
 * Delegates all computation to {@see PredictiveAnalyticsService} (core engine).
 */
class PredictiveAnalyticsController extends Controller
{
    public function __construct(
        private readonly PredictiveAnalyticsService $predictive,
        private readonly SportResolutionService $sportResolver,
    ) {}

    /**
     * GET /api/predictions/athlete?user_id=&sport_id=&horizon_days=
     */
    public function athletePrediction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'horizon_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $athlete = User::query()->findOrFail((int) $validated['user_id']);
        $this->assertCanViewAthletePredictions($request->user(), $athlete);

        $sport = $this->sportResolver->resolveForActor($request->user(), isset($validated['sport_id']) ? (int) $validated['sport_id'] : null);
        $horizonDays = (int) ($validated['horizon_days'] ?? 14);

        $prediction = $this->predictive->predictAthletePerformance($athlete, $sport, $horizonDays);

        return response()->json([
            'athlete' => ['id' => $athlete->id, 'name' => $athlete->name],
            'sport' => $sport ? ['id' => $sport->id, 'name' => $sport->name] : null,
            'prediction' => $prediction,
        ]);
    }

    /**
     * GET /api/predictions/team?sport_id=&team_a_user_ids[]=&team_b_user_ids[]=
     */
    public function teamPrediction(Request $request): JsonResponse
    {
        $actor = $request->user();

        $validated = $request->validate([
            'sport_id' => $this->sportIdRulesForTeamEndpoints($actor),
            'team_a_user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'team_a_user_ids.*' => ['integer', 'exists:users,id'],
            'team_b_user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'team_b_user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $sport = $this->sportResolver->resolveForActor($actor, isset($validated['sport_id']) ? (int) $validated['sport_id'] : null);
        $this->assertCanUseTeamRosterEndpoints($actor, $sport, [
            ...$validated['team_a_user_ids'],
            ...$validated['team_b_user_ids'],
        ]);

        $teamA = User::query()->whereIn('id', $validated['team_a_user_ids'])->get(['id', 'name', 'role']);
        $teamB = User::query()->whereIn('id', $validated['team_b_user_ids'])->get(['id', 'name', 'role']);

        $strengthA = $this->predictive->teamStrengthScore($teamA, $sport);
        $strengthB = $this->predictive->teamStrengthScore($teamB, $sport);
        $winA = $this->predictive->winProbability($strengthA, $strengthB);

        return response()->json([
            'sport' => $sport ? ['id' => $sport->id, 'name' => $sport->name] : null,
            'team_a' => [
                'strength' => $strengthA,
                'win_probability' => $winA,
                'members' => $teamA->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name]),
            ],
            'team_b' => [
                'strength' => $strengthB,
                'win_probability' => round(100.0 - $winA, 1),
                'members' => $teamB->map(fn (User $u) => ['id' => $u->id, 'name' => $u->name]),
            ],
        ]);
    }

    private function assertCanViewAthletePredictions(User $actor, User $target): void
    {
        abort_unless(RosterAccess::actorMayViewAthlete($actor, $target), 403);
    }

    /**
     * @return array<int, mixed>
     */
    private function sportIdRulesForTeamEndpoints(User $actor): array
    {
        return ['nullable', 'integer', 'exists:sports,id'];
    }

    /**
     * @param  list<int>  $userIds
     */
    private function assertCanUseTeamRosterEndpoints(User $actor, ?Sport $sport, array $userIds): void
    {
        if ($actor->organization_id === null) {
            abort(403);
        }

        $uniqueIds = array_values(array_unique($userIds));
        $users = User::query()->whereIn('id', $uniqueIds)->get(['id', 'organization_id', 'role']);
        if ($users->count() !== count($uniqueIds)) {
            abort(403);
        }

        foreach ($users as $u) {
            if ($u->organization_id === null || (int) $u->organization_id !== (int) $actor->organization_id) {
                abort(403);
            }
        }

        if ($actor->role === 'admin') {
            return;
        }

        if (! in_array($actor->role, ['coach'], true)) {
            abort(403);
        }

        if (! $sport instanceof Sport) {
            abort(403);
        }

        $allowedTeamStudentIds = CoachedTeams::coachedStudentIds($actor);

        foreach ($users as $u) {
            if ((int) $u->id === (int) $actor->id) {
                continue;
            }

            if (($u->role ?? 'student') !== 'student') {
                abort(403);
            }

            if (! $allowedTeamStudentIds->contains((int) $u->id)) {
                abort(403);
            }
        }

        if ($allowedTeamStudentIds->isEmpty()) {
            abort(403);
        }
    }
}
