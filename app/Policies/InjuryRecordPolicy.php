<?php

namespace App\Policies;

use App\Models\InjuryRecord;
use App\Models\User;
use App\Support\RosterAccess;

class InjuryRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null
            && in_array($user->role, ['admin', 'coach', 'instructor'], true);
    }

    public function view(User $user, InjuryRecord $record): bool
    {
        if ($user->organization_id === null || (int) $user->organization_id !== (int) $record->organization_id) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if (in_array($user->role, ['coach', 'instructor'], true)) {
            $athlete = $record->athlete;
            if (! $athlete) {
                return false;
            }

            return RosterAccess::actorMayViewAthlete($user, $athlete);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, InjuryRecord $record): bool
    {
        return $this->view($user, $record);
    }

    public function delete(User $user, InjuryRecord $record): bool
    {
        return $user->role === 'admin'
            && $user->organization_id !== null
            && (int) $user->organization_id === (int) $record->organization_id;
    }
}
