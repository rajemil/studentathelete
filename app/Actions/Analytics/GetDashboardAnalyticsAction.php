<?php

namespace App\Actions\Analytics;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\CoachedTeams;

class GetDashboardAnalyticsAction
{
    /**
     * Get sports and students based on the user's role and organization.
     *
     * @param  User  $user
     * @return array{sports: \Illuminate\Database\Eloquent\Collection, students: \Illuminate\Database\Eloquent\Collection}
     */
    public function execute(User $user): array
    {
        $sportsQuery = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->orderBy('name');

        $studentsQuery = User::query()
            ->where('organization_id', $user->organization_id)
            ->where('role', 'student')
            ->orderBy('name')
            ->limit(500);

        if (in_array($user->role, ['coach', 'instructor'], true)) {
            $sportIds = Team::query()
                ->whereIn('id', CoachedTeams::teamIds($user))
                ->pluck('sport_id')
                ->unique()
                ->filter();

            if ($sportIds->isEmpty()) {
                $sportsQuery->whereRaw('1 = 0');
            } else {
                $sportsQuery->whereIn('id', $sportIds);
            }

            $studentIds = CoachedTeams::coachedStudentIds($user);
            if ($studentIds->isEmpty()) {
                $studentsQuery->whereRaw('1 = 0');
            } else {
                $studentsQuery->whereIn('id', $studentIds);
            }
        }

        return [
            'sports' => $sportsQuery->get(['id', 'name']),
            'students' => $studentsQuery->get(['id', 'name', 'email']),
        ];
    }
}
