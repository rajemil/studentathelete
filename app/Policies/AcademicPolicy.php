<?php

namespace App\Policies;

use App\Models\User;
use App\Support\RosterAccess;

class AcademicPolicy
{
    /**
     * Determine if the user can view any academic/attendance records.
     */
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null && in_array($user->role, ['admin', 'coach', 'student'], true);
    }

    /**
     * Determine if the user can view the academic/attendance records of the athlete.
     */
    public function view(User $user, User $athlete): bool
    {
        return RosterAccess::actorMayViewAthlete($user, $athlete);
    }

    /**
     * Determine if the user can manage (create, edit, delete) academic/attendance records.
     */
    public function manage(User $user): bool
    {
        return $user->organization_id !== null && $user->role === 'admin';
    }
}
