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
        $this->authorize('assignStudents', $sport);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $student = User::query()
            ->where('role', 'student')
            ->where('organization_id', $request->user()->organization_id)
            ->findOrFail($validated['user_id']);

        $sport->students()->syncWithoutDetaching([$student->id]);

        activity()
            ->performedOn($sport)
            ->causedBy($request->user())
            ->withProperties(['student_id' => $student->id, 'action' => 'assigned'])
            ->log('sport_student_assigned');

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Student assigned to sport.');
    }

    public function destroy(Request $request, Sport $sport, User $user): RedirectResponse
    {
        $this->authorize('assignStudents', $sport);

        abort_unless($user->role === 'student', 404);
        abort_unless((int) $user->organization_id === (int) $request->user()->organization_id, 403);

        $sport->students()->detach($user->id);

        activity()
            ->performedOn($sport)
            ->causedBy($request->user())
            ->withProperties(['student_id' => $user->id, 'action' => 'removed'])
            ->log('sport_student_removed');

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Student removed from sport.');
    }
}
