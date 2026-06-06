<?php

namespace App\Actions\User;

use App\Actions\Team\AssignCoachAction;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CreateUserAction
{
    protected AssignCoachAction $assignCoachAction;

    public function __construct(AssignCoachAction $assignCoachAction)
    {
        $this->assignCoachAction = $assignCoachAction;
    }

    /**
     * Create a new faculty member (admin, coach).
     *
     * @param  array  $data
     * @param  int  $orgId
     * @param  User  $actor
     * @return User
     * @throws ValidationException
     */
    public function execute(array $data, int $orgId, User $actor): User
    {
        $password = $data['password'] ?: 'password';

        $user = User::query()->create([
            'organization_id' => $orgId,
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($password),
            'email_verified_at' => CarbonImmutable::now(),
        ]);

        $birthdate = isset($data['birthdate']) ? CarbonImmutable::parse($data['birthdate']) : null;

        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'birthdate' => $birthdate?->toDateString(),
            'gender' => $data['gender'] ?? null,
            'address' => isset($data['address']) ? mb_strtoupper($data['address']) : null,
            'profession' => $data['profession'] ?? null,
            'field_expertise' => $data['field_expertise'] ?? null,
            'achievements' => $data['achievements'] ?? null,
            'coaching_experience_years' => $data['coaching_experience_years'] ?? null,
        ]);

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $path = $data['photo']->store('faculty-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $allowedSportIds = Sport::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $desiredSportIds = collect($data['sport_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $id) => in_array($id, $allowedSportIds, true))
            ->unique()
            ->values();

        // Enforce: one faculty (coach) per sport.
        $alreadyAssignedSportIds = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->where('users.organization_id', $orgId)
            ->whereIn('users.role', ['coach'])
            ->whereIn('sport_user.sport_id', $desiredSportIds->all())
            ->distinct()
            ->pluck('sport_user.sport_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        if (! empty($alreadyAssignedSportIds)) {
            // Delete the user and profile to roll back if verification fails
            $profile->delete();
            $user->delete();
            throw ValidationException::withMessages([
                'sport_ids' => 'One or more selected sports are already assigned to another faculty member.',
            ]);
        }

        // Persist sport assignment (even if the sport currently has no teams).
        $user->sports()->syncWithoutDetaching($desiredSportIds->all());

        $teamIds = Team::query()
            ->where('organization_id', $orgId)
            ->whereIn('sport_id', $desiredSportIds)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        // Call our AssignCoachAction
        $this->assignCoachAction->execute($user, $teamIds, $teamIds);



        activity()
            ->performedOn($user)
            ->causedBy($actor)
            ->withProperties(['role' => $data['role']])
            ->log('faculty_created');

        return $user;
    }
}
