<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    /**
     * Admins and coaches of the same organization can view team members.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'coach']);
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return $user->organization_id === $teamMember->organization_id;
    }

    /**
     * Only admins may create, update, or delete team members.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return $user->role === 'admin'
            && $user->organization_id === $teamMember->organization_id;
    }

    public function delete(User $user, TeamMember $teamMember): bool
    {
        return $user->role === 'admin'
            && $user->organization_id === $teamMember->organization_id;
    }
}
