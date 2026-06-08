<?php

namespace App\Services\Sport;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\CoachedTeams;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for resolving sports and rosters for coaches and athletes.
 */
final class SportResolutionService
{
    /**
     * Sport IDs a coach may access (pivot, coached teams, primary column).
     *
     * @return Collection<int, int>
     */
    public function coachSportIds(User $coach): Collection
    {
        if ($coach->organization_id === null || $coach->role !== 'coach') {
            return collect();
        }

        $fromPivot = DB::table('sport_user')
            ->where('user_id', $coach->id)
            ->pluck('sport_id');

        $teamIds = CoachedTeams::teamIds($coach);
        $fromTeams = $teamIds->isEmpty()
            ? collect()
            : Team::query()->whereIn('id', $teamIds)->pluck('sport_id');

        return $fromPivot
            ->merge($fromTeams)
            ->when($coach->sport_id, fn (Collection $ids) => $ids->push($coach->sport_id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();
    }

    /**
     * Sport IDs an athlete participates in (pivot, active team memberships, primary column).
     *
     * @return Collection<int, int>
     */
    public function athleteSportIds(User $athlete): Collection
    {
        if ($athlete->role !== 'student') {
            return collect();
        }

        $fromPivot = DB::table('sport_user')
            ->where('user_id', $athlete->id)
            ->pluck('sport_id');

        $fromTeams = DB::table('team_memberships')
            ->join('teams', 'team_memberships.team_id', '=', 'teams.id')
            ->where('team_memberships.user_id', $athlete->id)
            ->whereNull('team_memberships.left_on')
            ->pluck('teams.sport_id');

        return $fromPivot
            ->merge($fromTeams)
            ->when($athlete->sport_id, fn (Collection $ids) => $ids->push($athlete->sport_id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();
    }

    /**
     * Resolve a sport for the actor, honoring an optional request value with safe fallbacks.
     */
    public function resolveForActor(User $actor, ?int $requestedSportId = null): ?Sport
    {
        if ($requestedSportId !== null) {
            $sport = Sport::query()->whereKey($requestedSportId)->first();
            if ($sport instanceof Sport && $this->actorMayAccessSport($actor, $sport)) {
                return $sport;
            }

            if ($actor->role === 'admin') {
                return null;
            }

            if (in_array($actor->role, ['coach', 'student'], true)) {
                return null;
            }
        }

        $fallbackId = match ($actor->role) {
            'coach' => $this->coachSportIds($actor)->first(),
            'student' => $this->athleteSportIds($actor)->first(),
            default => $requestedSportId,
        };

        if ($fallbackId === null) {
            return null;
        }

        $sport = Sport::query()->whereKey($fallbackId)->first();

        return $sport instanceof Sport && $this->actorMayAccessSport($actor, $sport)
            ? $sport
            : null;
    }

    public function actorMayAccessSport(User $actor, Sport $sport): bool
    {
        if ($actor->organization_id === null || $sport->organization_id === null) {
            return false;
        }

        if ((int) $actor->organization_id !== (int) $sport->organization_id) {
            return false;
        }

        return match ($actor->role) {
            'admin' => true,
            'coach' => $this->coachSportIds($actor)->contains((int) $sport->id),
            'student' => $this->athleteSportIds($actor)->contains((int) $sport->id),
            default => false,
        };
    }

    /**
     * Sports visible to the actor within their organization.
     *
     * @param  list<string>  $columns
     * @return EloquentCollection<int, Sport>
     */
    public function sportsForActor(User $actor, array $columns = ['id', 'name', 'slug']): EloquentCollection
    {
        if ($actor->organization_id === null) {
            return Sport::query()->whereRaw('1 = 0')->get($columns);
        }

        $query = Sport::query()
            ->where('organization_id', $actor->organization_id)
            ->orderBy('name');

        if ($actor->role === 'coach') {
            $sportIds = $this->coachSportIds($actor);
            if ($sportIds->isEmpty()) {
                return Sport::query()->whereRaw('1 = 0')->get($columns);
            }

            $query->whereIn('id', $sportIds);
        }

        return $query->get($columns);
    }

    /**
     * Student user IDs coached by the actor (teams + sport enrollments).
     *
     * @return Collection<int, int>
     */
    public function coachedStudentIds(User $coach): Collection
    {
        if ($coach->organization_id === null || $coach->role !== 'coach') {
            return collect();
        }

        $orgId = (int) $coach->organization_id;
        $teamIds = CoachedTeams::teamIds($coach);

        $fromTeams = collect();
        if ($teamIds->isNotEmpty()) {
            $fromTeams = DB::table('team_memberships')
                ->join('users', 'team_memberships.user_id', '=', 'users.id')
                ->whereIn('team_memberships.team_id', $teamIds)
                ->whereNull('team_memberships.left_on')
                ->where('users.organization_id', $orgId)
                ->where('users.role', 'student')
                ->distinct()
                ->pluck('team_memberships.user_id');
        }

        $sportIds = $this->coachSportIds($coach);
        $fromSports = collect();
        if ($sportIds->isNotEmpty()) {
            $fromSports = DB::table('sport_user')
                ->join('users', 'sport_user.user_id', '=', 'users.id')
                ->whereIn('sport_user.sport_id', $sportIds)
                ->where('users.organization_id', $orgId)
                ->where('users.role', 'student')
                ->distinct()
                ->pluck('sport_user.user_id');
        }

        return $fromTeams
            ->merge($fromSports)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * Students coached by the actor within a specific sport.
     *
     * @return Collection<int, int>
     */
    public function coachedStudentIdsForSport(User $coach, Sport $sport): Collection
    {
        if (! $this->actorMayAccessSport($coach, $sport)) {
            return collect();
        }

        $sportId = (int) $sport->id;
        $orgId = (int) $coach->organization_id;
        $teamIds = CoachedTeams::teamIds($coach);

        $fromTeams = collect();
        if ($teamIds->isNotEmpty()) {
            $fromTeams = DB::table('team_memberships')
                ->join('users', 'team_memberships.user_id', '=', 'users.id')
                ->join('teams', 'team_memberships.team_id', '=', 'teams.id')
                ->whereIn('team_memberships.team_id', $teamIds)
                ->where('teams.sport_id', $sportId)
                ->whereNull('team_memberships.left_on')
                ->where('users.organization_id', $orgId)
                ->where('users.role', 'student')
                ->distinct()
                ->pluck('team_memberships.user_id');
        }

        $fromSports = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->where('sport_user.sport_id', $sportId)
            ->where('users.organization_id', $orgId)
            ->where('users.role', 'student')
            ->distinct()
            ->pluck('sport_user.user_id');

        return $fromTeams
            ->merge($fromSports)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }

    /**
     * Coach user IDs assigned to any of the given sports (pivot + primary column + team coaching).
     *
     * @param  Collection<int, int>|array<int, int>  $sportIds
     * @return Collection<int, int>
     */
    public function coachIdsForSports(Collection|array $sportIds, ?int $organizationId = null): Collection
    {
        $ids = collect($sportIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $fromPivot = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->whereIn('sport_user.sport_id', $ids)
            ->where('users.role', 'coach')
            ->when($organizationId !== null, fn ($q) => $q->where('users.organization_id', $organizationId))
            ->pluck('users.id');

        $fromPrimary = User::query()
            ->where('role', 'coach')
            ->whereIn('sport_id', $ids)
            ->when($organizationId !== null, fn ($q) => $q->where('organization_id', $organizationId))
            ->pluck('id');

        $fromAssignmentCoaches = DB::table('coach_assignments')
            ->join('teams', 'coach_assignments.team_id', '=', 'teams.id')
            ->join('users', 'coach_assignments.coach_id', '=', 'users.id')
            ->whereIn('teams.sport_id', $ids)
            ->where('users.role', 'coach')
            ->when($organizationId !== null, fn ($q) => $q->where('teams.organization_id', $organizationId))
            ->distinct()
            ->pluck('coach_assignments.coach_id');

        $fromPrimaryCoaches = DB::table('teams')
            ->join('users', 'teams.primary_coach_id', '=', 'users.id')
            ->whereIn('teams.sport_id', $ids)
            ->where('users.role', 'coach')
            ->when($organizationId !== null, fn ($q) => $q->where('teams.organization_id', $organizationId))
            ->whereNotNull('teams.primary_coach_id')
            ->distinct()
            ->pluck('teams.primary_coach_id');

        return $fromPivot
            ->merge($fromPrimary)
            ->merge($fromAssignmentCoaches)
            ->merge($fromPrimaryCoaches)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }
}
