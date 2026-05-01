<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' && $user->organization_id !== null;
    }

    public function view(User $user, Team $team): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $team);
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin' && $user->organization_id !== null;
    }

    public function update(User $user, Team $team): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $team);
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $team);
    }

    private function sameOrganization(User $user, Team $team): bool
    {
        return $user->organization_id !== null
            && $team->organization_id !== null
            && (int) $user->organization_id === (int) $team->organization_id;
    }
}
