<?php

namespace App\Policies;

use App\Models\ParticipationLog;
use App\Models\User;
use App\Support\RosterAccess;

class ParticipationLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null;
    }

    public function view(User $user, ParticipationLog $log): bool
    {
        if ($user->organization_id === null || (int) $user->organization_id !== (int) $log->organization_id) {
            return false;
        }

        if ($user->role === 'student') {
            return (int) $user->id === (int) $log->user_id;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if (in_array($user->role, ['coach', 'instructor'], true)) {
            $athlete = $log->user;
            if (! $athlete) {
                return false;
            }

            return RosterAccess::actorMayViewAthlete($user, $athlete);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->organization_id !== null && $user->role === 'student';
    }

    public function update(User $user, ParticipationLog $log): bool
    {
        return $user->role === 'student' && (int) $user->id === (int) $log->user_id;
    }

    public function delete(User $user, ParticipationLog $log): bool
    {
        return $this->update($user, $log);
    }
}
