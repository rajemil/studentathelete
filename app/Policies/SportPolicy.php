<?php

namespace App\Policies;

use App\Models\Sport;
use App\Models\User;

class SportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null
            && in_array($user->role, ['admin', 'coach', 'instructor', 'student'], true);
    }

    public function view(User $user, Sport $sport): bool
    {
        if (! $this->sameOrganization($user, $sport)) {
            return false;
        }

        return match ($user->role) {
            'admin' => true,
            'coach', 'instructor' => true,
            'student' => $user->sports()->where('sports.id', $sport->id)->exists(),
            default => false,
        };
    }

    public function create(User $user): bool
    {
        return $user->organization_id !== null
            && in_array($user->role, ['admin', 'coach', 'instructor'], true);
    }

    public function update(User $user, Sport $sport): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $sport);
    }

    public function delete(User $user, Sport $sport): bool
    {
        return $user->role === 'admin' && $this->sameOrganization($user, $sport);
    }

    public function assignStudents(User $user, Sport $sport): bool
    {
        if (! $this->sameOrganization($user, $sport)) {
            return false;
        }

        return in_array($user->role, ['admin', 'coach', 'instructor'], true);
    }

    public function recordScores(User $user, Sport $sport): bool
    {
        return $this->assignStudents($user, $sport);
    }

    public function analytics(User $user, Sport $sport): bool
    {
        return $this->view($user, $sport);
    }

    private function sameOrganization(User $user, Sport $sport): bool
    {
        return $user->organization_id !== null
            && $sport->organization_id !== null
            && (int) $user->organization_id === (int) $sport->organization_id;
    }
}
