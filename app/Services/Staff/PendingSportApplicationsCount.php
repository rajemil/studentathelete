<?php

namespace App\Services\Staff;

use App\Models\Sport;
use App\Models\SportApplication;
use App\Models\User;
use App\Support\CoachedTeams;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Pending sport-application badge count for staff navigation.
 *
 * Kept out of AppServiceProvider boot logic: no nested whereHas, no per-include queries.
 */
final class PendingSportApplicationsCount
{
    private const CACHE_SECONDS = 120;

    public static function forUser(User $user): int
    {
        if (! in_array($user->role, ['admin', 'coach', 'instructor'], true)) {
            return 0;
        }

        if ($user->organization_id === null) {
            return 0;
        }

        return (int) Cache::remember(
            self::cacheKey($user),
            self::CACHE_SECONDS,
            fn () => self::countUncached($user),
        );
    }

    public static function forgetForUser(User|int $user): void
    {
        $user = $user instanceof User ? $user : User::query()->find($user);
        if ($user) {
            Cache::forget(self::cacheKey($user));
        }
    }

    public static function forgetForOrganization(int $organizationId): void
    {
        $admin = User::query()
            ->where('organization_id', $organizationId)
            ->where('role', 'admin')
            ->select('id', 'role', 'organization_id')
            ->get();

        foreach ($admin as $user) {
            Cache::forget(self::cacheKey($user));
        }

        User::query()
            ->where('organization_id', $organizationId)
            ->whereIn('role', ['coach', 'instructor'])
            ->select('id', 'role', 'organization_id')
            ->cursor()
            ->each(fn (User $user) => Cache::forget(self::cacheKey($user)));
    }

    private static function cacheKey(User $user): string
    {
        return sprintf('pending_sport_applications:%d:%s', $user->id, $user->role);
    }

    private static function countUncached(User $user): int
    {
        $orgId = (int) $user->organization_id;

        if ($user->role === 'admin') {
            return SportApplication::query()
                ->where('status', 'pending')
                ->whereIn('sport_id', Sport::query()->where('organization_id', $orgId)->select('id'))
                ->count();
        }

        $sportIds = self::managedSportIds($user);
        if ($sportIds->isEmpty()) {
            return 0;
        }

        return SportApplication::query()
            ->where('status', 'pending')
            ->whereIn('sport_id', $sportIds)
            ->count();
    }

    /**
     * Sport IDs this staff member may review (matches notification scope, without loading applications).
     */
    public static function managedSportIds(User $user): Collection
    {
        $orgId = (int) $user->organization_id;
        $ids = collect();

        $pivotSportIds = DB::table('sport_user')
            ->where('user_id', $user->id)
            ->pluck('sport_id');
        $ids = $ids->merge($pivotSportIds);

        if ($user->role === 'instructor') {
            $ids = $ids->merge(
                Sport::query()
                    ->where('organization_id', $orgId)
                    ->where('instructor_user_id', $user->id)
                    ->pluck('id')
            );
        }

        $teamIds = CoachedTeams::teamIds($user);
        if ($teamIds->isNotEmpty()) {
            $ids = $ids->merge(
                DB::table('teams')
                    ->whereIn('id', $teamIds)
                    ->whereNotNull('sport_id')
                    ->pluck('sport_id')
            );
        }

        return $ids->unique()->values();
    }
}
