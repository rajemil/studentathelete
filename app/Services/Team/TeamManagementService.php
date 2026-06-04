<?php

namespace App\Services\Team;

use App\Actions\Team\AssignCoachAction;
use App\Models\Sport;
use App\Models\SportApplication;
use App\Models\User;

class TeamManagementService
{
    protected AssignCoachAction $assignCoachAction;

    public function __construct(AssignCoachAction $assignCoachAction)
    {
        $this->assignCoachAction = $assignCoachAction;
    }

    /**
     * Assign coach to teams.
     *
     * @param  User  $coach
     * @param  array<int>  $desiredTeamIds
     * @param  array<int>  $allowedTeamIds
     * @return void
     */
    public function assignCoach(User $coach, array $desiredTeamIds, array $allowedTeamIds): void
    {
        $this->assignCoachAction->execute($coach, $desiredTeamIds, $allowedTeamIds);
    }

    /**
     * Assign student to a sport.
     *
     * @param  User  $student
     * @param  Sport  $sport
     * @param  User  $actor
     * @return void
     */
    public function assignStudentToSport(User $student, Sport $sport, User $actor): void
    {
        $sport->students()->syncWithoutDetaching([$student->id]);

        SportApplication::query()
            ->where('sport_id', $sport->id)
            ->where('user_id', $student->id)
            ->delete();

        activity()
            ->performedOn($sport)
            ->causedBy($actor)
            ->withProperties(['student_id' => $student->id, 'action' => 'assigned'])
            ->log('sport_student_assigned');
    }

    /**
     * Remove student from a sport.
     *
     * @param  User  $student
     * @param  Sport  $sport
     * @param  User  $actor
     * @return void
     */
    public function removeStudentFromSport(User $student, Sport $sport, User $actor): void
    {
        $sport->students()->detach($student->id);

        activity()
            ->performedOn($sport)
            ->causedBy($actor)
            ->withProperties(['student_id' => $student->id, 'action' => 'removed'])
            ->log('sport_student_removed');
    }
}
