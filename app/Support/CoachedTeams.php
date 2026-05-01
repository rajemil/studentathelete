<?php

namespace App\Support;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class CoachedTeams
{
    /**
     * Team IDs where the user is primary coach or assigned as coach (same organization only).
     */
    public static function teamIds(User $user): Collection
    {
        if ($user->organization_id === null) {
            return collect();
        }

        return Team::query()
            ->where('organization_id', $user->organization_id)
            ->where(function ($q) use ($user) {
                $q->where('primary_coach_id', $user->id)
                    ->orWhereHas('coachAssignments', fn ($c) => $c->where('coach_id', $user->id));
            })
            ->pluck('id');
    }

    /**
     * Student user IDs on teams coached by the user (for this organization).
     */
    public static function coachedStudentIds(User $user): Collection
    {
        $teamIds = self::teamIds($user);
        if ($teamIds->isEmpty()) {
            return collect();
        }

        return DB::table('team_memberships')
            ->whereIn('team_id', $teamIds)
            ->distinct()
            ->pluck('user_id');
    }
}
