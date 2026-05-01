<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' && $user->organization_id !== null;
    }

    public function view(User $user, User $model): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $model);
    }

    public function updateRole(User $user, User $model): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $model);
    }

    private function sameOrganization(User $user, User $model): bool
    {
        return $user->organization_id !== null
            && $model->organization_id !== null
            && (int) $user->organization_id === (int) $model->organization_id;
    }
}
