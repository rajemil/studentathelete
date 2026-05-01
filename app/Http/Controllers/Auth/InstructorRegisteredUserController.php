<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstructorRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register-instructor');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            'age' => ['required', 'integer', 'min:18', 'max:90'],
            'gender' => ['required', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:255'],
            'field_expertise' => ['required', 'string', 'max:255'],
            'achievements' => ['nullable', 'string', 'max:5000'],
            'profession' => ['required', 'string', 'max:255'],
            'coaching_experience_years' => ['required', 'integer', 'min:0', 'max:70'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'organization_id' => Organization::defaultId(),
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'instructor',
                'password' => Hash::make($validated['password']),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'age' => $validated['age'],
                'gender' => $validated['gender'],
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

        return redirect()->route('dashboard');
    }
}
