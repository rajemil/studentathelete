<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Support\CoachedTeams;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->organization_id !== null && in_array($user->role, ['admin', 'coach'], true);
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->organization_id === null) {
            return false;
        }

        if ($user->role === 'admin') {
            return $this->sameOrganization($user, $event);
        }

        if (in_array($user->role, ['coach'], true)) {
            if (!$this->sameOrganization($user, $event)) {
                return false;
            }
            if ($event->team_id !== null) {
                return CoachedTeams::teamIds($user)->contains((int) $event->team_id);
            }
            return (int) $event->created_by === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->organization_id !== null && in_array($user->role, ['admin', 'coach'], true);
    }

    public function update(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->view($user, $event);
    }

    private function sameOrganization(User $user, Event $event): bool
    {
        if ($event->team_id !== null) {
            return $event->team && (int) $event->team->organization_id === (int) $user->organization_id;
        }
        if ($event->sport_id !== null) {
            return $event->sport && (int) $event->sport->organization_id === (int) $user->organization_id;
        }
        if ($event->created_by !== null) {
            return $event->creator && (int) $event->creator->organization_id === (int) $user->organization_id;
        }
        return false;
    }
}
