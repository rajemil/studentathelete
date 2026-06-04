<?php

namespace App\Actions\Team;

use App\Models\CoachAssignment;
use App\Models\User;
use Carbon\CarbonImmutable;

class AssignCoachAction
{
    /**
     * Sync coach assignments for the coach to the given teams, scoped by allowed teams.
     *
     * @param  User  $coach
     * @param  array<int>  $desiredTeamIds
     * @param  array<int>  $allowedTeamIds
     * @return void
     */
    public function execute(User $coach, array $desiredTeamIds, array $allowedTeamIds): void
    {
        // Sync "coach" assignments only (keeps primary coach and other roles intact).
        CoachAssignment::query()
            ->where('coach_id', $coach->id)
            ->where('assignment_role', 'coach')
            ->whereIn('team_id', $allowedTeamIds)
            ->whereNotIn('team_id', $desiredTeamIds)
            ->delete();

        $existing = CoachAssignment::query()
            ->where('coach_id', $coach->id)
            ->where('assignment_role', 'coach')
            ->whereIn('team_id', $allowedTeamIds)
            ->pluck('team_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $toAdd = collect($desiredTeamIds)->filter(fn (int $id) => ! in_array($id, $existing, true));

        foreach ($toAdd as $teamId) {
            CoachAssignment::query()->firstOrCreate([
                'coach_id' => $coach->id,
                'team_id' => $teamId,
                'assignment_role' => 'coach',
            ], [
                'starts_on' => CarbonImmutable::now()->toDateString(),
                'ends_on' => null,
            ]);
        }
    }
}
