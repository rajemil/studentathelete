<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SportStudentController extends Controller
{
    public function store(Request $request, Sport $sport): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $student = User::query()->where('role', 'student')->findOrFail($validated['user_id']);

        $sport->students()->syncWithoutDetaching([$student->id]);

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Student assigned to sport.');
    }

    public function destroy(Sport $sport, User $user): RedirectResponse
    {
        abort_unless($user->role === 'student', 404);

        $sport->students()->detach($user->id);

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Student removed from sport.');
    }
}
