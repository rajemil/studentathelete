<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Sport;
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

class StudentRegisteredUserController extends Controller
{
    public function create(): View
    {
        $sports = Sport::query()->orderBy('name')->get(['id', 'name']);

        return view('auth.register-student', compact('sports'));
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

            'age' => ['required', 'integer', 'min:10', 'max:80'],
            'gender' => ['required', 'string', 'max:32'],
            'address' => ['required', 'string', 'max:255'],
            'height_cm' => ['required', 'numeric', 'min:50', 'max:300'],
            'weight_kg' => ['required', 'numeric', 'min:10', 'max:350'],
            'sports_interested' => ['nullable', 'array', 'max:20'],
            'sports_interested.*' => ['integer', 'exists:sports,id'],
        ]);

        $bmi = $this->bmi((float) $validated['height_cm'], (float) $validated['weight_kg']);

        $user = DB::transaction(function () use ($validated, $bmi) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => 'student',
                'password' => Hash::make($validated['password']),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'height_cm' => $validated['height_cm'],
                'weight_kg' => $validated['weight_kg'],
                'bmi' => $bmi,
                'sports_interested' => $validated['sports_interested'] ?? [],
            ]);

            if (! empty($validated['sports_interested'])) {
                $user->sports()->sync($validated['sports_interested']);
            }

            return $user;
        });

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    private function bmi(float $heightCm, float $weightKg): float
    {
        $m = $heightCm / 100.0;
        if ($m <= 0) return 0.0;
        return round($weightKg / ($m * $m), 2);
    }
}
