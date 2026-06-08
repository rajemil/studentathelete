<?php

namespace App\Support;

use App\Models\Sport;
use App\Models\User;
use App\Services\Sport\SportResolutionService;
use Illuminate\Support\Collection;

final class RosterAccess
{
    public static function sameOrganization(User $actor, User $target): bool
    {
        return $actor->organization_id !== null
            && $target->organization_id !== null
            && (int) $actor->organization_id === (int) $target->organization_id;
    }

    public static function sameOrganizationSport(User $actor, Sport $sport): bool
    {
        return $actor->organization_id !== null
            && $sport->organization_id !== null
            && (int) $actor->organization_id === (int) $sport->organization_id;
    }

    public static function coachedStudentIds(User $actor): Collection
    {
        return CoachedTeams::coachedStudentIds($actor)->map(fn ($id) => (int) $id);
    }

    public static function actorCoachesSport(User $actor, Sport $sport): bool
    {
        if (! in_array($actor->role, ['coach'], true)) {
            return false;
        }

        return app(SportResolutionService::class)->actorMayAccessSport($actor, $sport);
    }

    /**
     * Used for endpoints that expose detailed personal analytics/predictions.
     */
    public static function actorMayViewAthlete(User $actor, User $athlete): bool
    {
        $role = $actor->role ?? 'student';

        if ($role === 'student') {
            return (int) $actor->id === (int) $athlete->id;
        }

        if (! self::sameOrganization($actor, $athlete)) {
            return false;
        }

        if ($role === 'admin') {
            return true;
        }

        if (in_array($role, ['coach'], true)) {
            return self::coachedStudentIds($actor)->contains((int) $athlete->id);
        }

        return false;
    }

    /**
     * Used for score entry.
     *
     * Policy: admin OK; coach OK if same org and athlete is enrolled in sport.
     * If teams exist for that sport, you can tighten this later to require coached roster.
     */
    public static function actorMayEnterScoreFor(User $actor, User $athlete, Sport $sport): bool
    {
        if (! self::sameOrganization($actor, $athlete) || ! self::sameOrganizationSport($actor, $sport)) {
            return false;
        }

        if (! $sport->students()->whereKey($athlete->id)->exists()) {
            return false;
        }

        if ($actor->role === 'admin') {
            return true;
        }

        return in_array($actor->role, ['coach'], true);
    }
}
