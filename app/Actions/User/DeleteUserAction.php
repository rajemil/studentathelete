<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteUserAction
{
    /**
     * Delete a faculty member.
     *
     * @param  User  $user
     * @param  int  $orgId
     * @param  User  $actor
     * @return void
     * @throws ValidationException
     */
    public function execute(User $user, int $orgId, User $actor): void
    {
        // Prevent deleting the last admin in the organization.
        if ($user->role === 'admin') {
            $admins = User::query()
                ->where('organization_id', $orgId)
                ->where('role', 'admin')
                ->count();

            if ($admins <= 1) {
                throw ValidationException::withMessages([
                    'delete' => 'You cannot delete the only administrator.',
                ]);
            }
        }

        if ($user->profile?->photo_path) {
            Storage::disk('public')->delete($user->profile->photo_path);
        }

        activity()
            ->performedOn($user)
            ->causedBy($actor)
            ->log('faculty_deleted');

        $user->delete();
    }
}
