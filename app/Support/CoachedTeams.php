<?php

namespace App\Support;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
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
     * Student user IDs coached by the user (teams + sport enrollments).
     */
    public static function coachedStudentIds(User $user): Collection
    {
        return app(\App\Services\Sport\SportResolutionService::class)
            ->coachedStudentIds($user);
    }
}
