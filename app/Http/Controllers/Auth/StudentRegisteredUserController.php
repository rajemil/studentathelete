<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use App\Support\PersonName;
use App\Support\RegistrationRules;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentRegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $invitedUser = $this->resolveInvitedUser($request->query('token'));

        $orgId = $invitedUser?->organization_id ?? Organization::defaultId();

        $sports = Sport::query()
            ->where('organization_id', $orgId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('auth.register-student', [
            'sports' => $sports,
            'invitedUser' => $invitedUser,
            'invitationToken' => $request->query('token'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $invitedUser = $this->resolveInvitedUser($request->input('invitation_token'));

        if ($invitedUser) {
            return $this->completeInvitationRegistration($request, $invitedUser);
        }

        return $this->registerNewStudent($request);
    }

    private function registerNewStudent(Request $request): RedirectResponse
    {
        $orgId = Organization::defaultId();

        $validated = $request->validate(array_merge(
            RegistrationRules::nameFields(),
            [
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            ],
            RegistrationRules::passwordRequired(),
            RegistrationRules::studentProfileFields(true),
            RegistrationRules::sportsInterested($orgId),
        ));

        $user = DB::transaction(function () use ($validated, $orgId) {
            $user = User::create([
                'organization_id' => $orgId,
                'name' => PersonName::combine($validated['first_name'], $validated['last_name']),
                'email' => $validated['email'],
                'role' => 'student',
                'password' => Hash::make($validated['password']),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'birthdate' => CarbonImmutable::parse($validated['birthdate'])->toDateString(),
                'gender' => RegistrationRules::normalizeGender($validated['gender']),
                'address' => $validated['address'],
                'course' => $validated['course'],
                'height_cm' => $validated['height_cm'],
                'weight_kg' => $validated['weight_kg'],
                'sports_interested' => $validated['sports_interested'] ?? [],
            ]);

            if (! empty($validated['sports_interested'])) {
                $user->sports()->sync(
                    Sport::query()
                        ->where('organization_id', $orgId)
                        ->whereIn('id', $validated['sports_interested'])
                        ->pluck('id')
                );
            }

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    private function completeInvitationRegistration(Request $request, User $invitedUser): RedirectResponse
    {
        $orgId = (int) $invitedUser->organization_id;

        $validated = $request->validate(array_merge(
            RegistrationRules::nameFields(),
            [
                'email' => [
                    'required',
                    'string',
                    'lowercase',
                    'email',
                    'max:255',
                    Rule::unique(User::class)->ignore($invitedUser->id),
                ],
                'invitation_token' => ['required', 'string'],
            ],
            RegistrationRules::passwordRequired(),
            RegistrationRules::studentProfileFields(true),
            RegistrationRules::sportsInterested($orgId),
        ));

        if (! hash_equals((string) $invitedUser->invitation_token, (string) $validated['invitation_token'])) {
            throw ValidationException::withMessages([
                'invitation_token' => 'This invitation link is invalid or has already been used.',
            ]);
        }

        if (strtolower($validated['email']) !== strtolower($invitedUser->email)) {
            throw ValidationException::withMessages([
                'email' => 'Email must match the invited student athlete account.',
            ]);
        }

        $user = DB::transaction(function () use ($validated, $invitedUser, $orgId) {
            $invitedUser->update([
                'name' => PersonName::combine($validated['first_name'], $validated['last_name']),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'invitation_token' => null,
                'invited_at' => $invitedUser->invited_at,
            ]);

            $profile = $invitedUser->profile ?: Profile::query()->create(['user_id' => $invitedUser->id]);
            $profile->fill([
                'birthdate' => CarbonImmutable::parse($validated['birthdate'])->toDateString(),
                'gender' => RegistrationRules::normalizeGender($validated['gender']),
                'address' => $validated['address'],
                'course' => $validated['course'],
                'height_cm' => $validated['height_cm'],
                'weight_kg' => $validated['weight_kg'],
                'sports_interested' => $validated['sports_interested'] ?? [],
            ])->save();

            if (! empty($validated['sports_interested'])) {
                $invitedUser->sports()->sync(
                    Sport::query()
                        ->where('organization_id', $orgId)
                        ->whereIn('id', $validated['sports_interested'])
                        ->pluck('id')
                );
            }

            return $invitedUser->fresh();
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    private function resolveInvitedUser(?string $token): ?User
    {
        if ($token === null || $token === '') {
            return null;
        }

        return User::withoutGlobalScopes()
            ->where('role', 'student')
            ->where('invitation_token', $token)
            ->first();
    }
}
