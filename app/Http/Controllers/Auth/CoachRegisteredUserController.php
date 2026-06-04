<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CoachRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register-coach');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(array_merge(
            RegistrationRules::nameFields(),
            [
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            ],
            RegistrationRules::passwordRequired(),
            RegistrationRules::facultyProfileFields(true),
            [
                'achievements' => ['nullable', 'string', 'max:5000'],
            ],
        ));

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'organization_id' => Organization::defaultId(),
                'name' => PersonName::combine($validated['first_name'], $validated['last_name']),
                'email' => $validated['email'],
                'role' => 'coach',
                'password' => Hash::make($validated['password']),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'birthdate' => CarbonImmutable::parse($validated['birthdate'])->toDateString(),
                'gender' => RegistrationRules::normalizeGender($validated['gender']),
                'address' => $validated['address'],
                'field_expertise' => $validated['field_expertise'],
                'achievements' => $validated['achievements'] ?? null,
                'profession' => $validated['profession'],
                'coaching_experience_years' => $validated['coaching_experience_years'],
            ]);

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
