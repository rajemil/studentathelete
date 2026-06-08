<?php

namespace App\Actions\Analytics;

use App\Models\Sport;
use App\Models\User;
use App\Services\Sport\SportResolutionService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class GetDashboardAnalyticsAction
{
    public function __construct(
        private readonly SportResolutionService $sportResolver,
    ) {}

    /**
     * @return array{sports: EloquentCollection, students: EloquentCollection, selectedSportId: ?int}
     */
    public function execute(User $user, ?int $sportFilter = null): array
    {
        $sports = $this->sportResolver->sportsForActor($user, ['id', 'name']);

        if ($user->role === 'student') {
            return [
                'sports' => $sports,
                'students' => User::query()
                    ->whereKey($user->id)
                    ->get(['id', 'name', 'email']),
                'selectedSportId' => $this->resolveStudentSportFilter($user, $sportFilter),
            ];
        }

        $studentsQuery = User::query()
            ->where('organization_id', $user->organization_id)
            ->where('role', 'student')
            ->orderBy('name')
            ->limit(500);

        $selectedSportId = null;

        if ($user->role === 'coach') {
            $selectedSportId = $this->resolveCoachSportFilter($user, $sportFilter, $sports);

            if ($selectedSportId !== null) {
                $sport = Sport::query()->find($selectedSportId);
                $studentIds = $sport instanceof Sport
                    ? $this->sportResolver->coachedStudentIdsForSport($user, $sport)
                    : collect();
            } else {
                $studentIds = $this->sportResolver->coachedStudentIds($user);
            }

            if ($studentIds->isEmpty()) {
                $studentsQuery->whereRaw('1 = 0');
            } else {
                $studentsQuery->whereIn('id', $studentIds);
            }
        }

        return [
            'sports' => $sports,
            'students' => $studentsQuery->get(['id', 'name', 'email']),
            'selectedSportId' => $selectedSportId,
        ];
    }

    private function resolveCoachSportFilter(User $coach, ?int $sportFilter, EloquentCollection $sports): ?int
    {
        if ($sportFilter === null) {
            return null;
        }

        $sport = Sport::query()->find($sportFilter);
        if (! $sport instanceof Sport || ! $this->sportResolver->actorMayAccessSport($coach, $sport)) {
            return null;
        }

        return (int) $sport->id;
    }

    private function resolveStudentSportFilter(User $student, ?int $sportFilter): ?int
    {
        if ($sportFilter === null) {
            return null;
        }

        $allowed = $this->sportResolver->athleteSportIds($student);
        if (! $allowed->contains($sportFilter)) {
            return null;
        }

        return $sportFilter;
    }
}
