<?php

namespace App\Actions\User;

use App\Actions\Team\AssignCoachAction;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UpdateUserAction
{
    protected AssignCoachAction $assignCoachAction;

    public function __construct(AssignCoachAction $assignCoachAction)
    {
        $this->assignCoachAction = $assignCoachAction;
    }

    /**
     * Update an existing faculty member.
     *
     * @param  User  $user
     * @param  array  $data
     * @param  int  $orgId
     * @param  User  $actor
     * @return User
     * @throws ValidationException
     */
    public function execute(User $user, array $data, int $orgId, User $actor): User
    {
        if ($user->role === 'admin' && $data['role'] !== 'admin') {
            $admins = User::query()
                ->where('organization_id', $orgId)
                ->where('role', 'admin')
                ->count();

            if ($admins <= 1) {
                throw ValidationException::withMessages([
                    'role' => 'You cannot demote the only administrator.',
                ]);
            }
        }

        $updateData = [
            'name' => mb_strtoupper($data['name']),
            'email' => $data['email'],
            'role' => $data['role'],
        ];

        if (! empty($data['password'])) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        $user->update($updateData);

        $birthdate = isset($data['birthdate']) ? CarbonImmutable::parse($data['birthdate']) : null;

        $profile = $user->profile ?: Profile::query()->create(['user_id' => $user->id]);
        $profile->fill([
            'birthdate' => $birthdate?->toDateString(),
            'gender' => $data['gender'] ?? null,
            'address' => isset($data['address']) ? mb_strtoupper($data['address']) : null,
            'profession' => $data['profession'] ?? null,
            'field_expertise' => $data['field_expertise'] ?? null,
            'achievements' => $data['achievements'] ?? null,
            'coaching_experience_years' => $data['coaching_experience_years'] ?? null,
        ])->save();

        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
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

        // Enforce: one faculty (coach) per sport (allow keeping already-owned sports).
        $alreadyAssignedToOther = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->where('users.organization_id', $orgId)
            ->whereIn('users.role', ['coach'])
            ->where('sport_user.user_id', '!=', $user->id)
            ->whereIn('sport_user.sport_id', $desiredSportIds->all())
            ->exists();

        if ($alreadyAssignedToOther) {
            throw ValidationException::withMessages([
                'sport_ids' => 'One or more selected sports are already assigned to another faculty member.',
            ]);
        }

        // Keep the faculty's sport assignments in sync.
        $user->sports()->sync($desiredSportIds->all());

        $allowedTeamIds = Team::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $desiredTeamIds = Team::query()
            ->where('organization_id', $orgId)
            ->whereIn('sport_id', $desiredSportIds)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        // Sync coach assignments
        $this->assignCoachAction->execute($user, $desiredTeamIds, $allowedTeamIds);



        activity()
            ->performedOn($user)
            ->causedBy($actor)
            ->withProperties([
                'new_role' => $data['role'],
                'sport_ids' => $desiredSportIds->all(),
            ])
            ->log('faculty_updated');

        return $user;
    }
}
