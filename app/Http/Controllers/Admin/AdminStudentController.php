<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentWelcomeMail;
use App\Notifications\StudentInvitationNotification;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use App\Support\PersonName;
use App\Support\RegistrationRules;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $orgId = auth()->user()->organization_id;

        $students = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->with(['sports', 'profile'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $sports = Sport::query()
            ->where('organization_id', $orgId)
            ->orderBy('name')
            ->get();

        return view('admin.students.index', compact('students', 'sports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $orgId = (int) $request->user()->organization_id;

        $validated = $request->validate(array_merge(
            RegistrationRules::nameFields(),
            [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ],
            RegistrationRules::passwordRequired(),
            RegistrationRules::studentProfileFields(true),
            RegistrationRules::sportIds($orgId),
            [
                'photo' => ['nullable', 'file', 'image', 'max:5120'],
            ],
        ));

        $invitationToken = Str::random(64);

        $user = User::query()->create([
            'organization_id' => $orgId,
            'name' => PersonName::combine($validated['first_name'], $validated['last_name']),
            'email' => $validated['email'],
            'role' => 'student',
            'password' => Hash::make($validated['password']),
            'invitation_token' => $invitationToken,
            'invited_at' => now(),
        ]);

        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'birthdate' => CarbonImmutable::parse($validated['birthdate'])->toDateString(),
            'gender' => RegistrationRules::normalizeGender($validated['gender']),
            'address' => $validated['address'],
            'course' => $validated['course'],
            'height_cm' => (float) $validated['height_cm'],
            'weight_kg' => (float) $validated['weight_kg'],
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('student-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $user->sports()->sync($this->filterSportIds($validated['sport_ids'] ?? [], $orgId));

        event(new Registered($user));
        $user->sendEmailVerificationNotification();

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('student_created');

        try {
            $user->notify(new StudentInvitationNotification($invitationToken));
            Mail::to($user->email)->send(new StudentWelcomeMail($user));
        } catch (\Throwable $e) {
            Log::error('student_welcome_mail_failed', ['user_id' => $user->id, 'exception' => $e->getMessage()]);

            return redirect()->route('admin.students.index')
                ->with('status', 'Student created. Verification email was queued; welcome email could not be sent.');
        }

        return redirect()->route('admin.students.index')
            ->with('status', 'Student account created. They must verify their email before signing in.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureStudent($user);
        $this->authorize('updateRole', $user);

        $orgId = (int) $request->user()->organization_id;

        $validated = $request->validate(array_merge(
            RegistrationRules::nameFields(),
            [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            ],
            RegistrationRules::passwordOptional(),
            RegistrationRules::studentProfileFields(true),
            RegistrationRules::sportIds($orgId),
            [
                'photo' => ['nullable', 'file', 'image', 'max:5120'],
            ],
        ));

        $user->update([
            'name' => PersonName::combine($validated['first_name'], $validated['last_name']),
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $profile = $user->profile ?: Profile::query()->create(['user_id' => $user->id]);
        $profile->fill([
            'birthdate' => CarbonImmutable::parse($validated['birthdate'])->toDateString(),
            'gender' => RegistrationRules::normalizeGender($validated['gender']),
            'address' => $validated['address'],
            'course' => $validated['course'],
            'height_cm' => (float) $validated['height_cm'],
            'weight_kg' => (float) $validated['weight_kg'],
        ])->save();

        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $path = $request->file('photo')->store('student-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $user->sports()->sync($this->filterSportIds($validated['sport_ids'] ?? [], $orgId));

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('student_updated');

        return redirect()->route('admin.students.index')
            ->with('status', 'Student updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureStudent($user);
        $this->authorize('delete', $user);

        if ($user->profile?->photo_path) {
            Storage::disk('public')->delete($user->profile->photo_path);
        }

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('student_deleted');

        $user->delete();

        return redirect()->route('admin.students.index')
            ->with('status', 'Student removed.');
    }

    /**
     * @param  list<int|string>  $ids
     * @return list<int>
     */
    private function filterSportIds(array $ids, int $orgId): array
    {
        $allowed = Sport::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $allowedSet = array_flip($allowed);

        return collect($ids)
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $id) => isset($allowedSet[$id]))
            ->unique()
            ->values()
            ->all();
    }

    private function ensureStudent(User $user): void
    {
        abort_unless(
            $user->role === 'student'
            && (int) $user->organization_id === (int) auth()->user()->organization_id,
            404
        );
    }
}
