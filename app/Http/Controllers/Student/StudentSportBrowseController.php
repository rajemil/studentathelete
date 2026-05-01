<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StudentSportBrowseController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $joinedIds = $user->sports()->pluck('sports.id');

        $sports = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->withCount('students')
            ->orderBy('name')
            ->paginate(12);

        return view('student.sports.index', compact('sports', 'joinedIds'));
    }

    public function join(Sport $sport): RedirectResponse
    {
        $user = auth()->user();
        abort_unless((int) $sport->organization_id === (int) $user->organization_id, 403);

        $user->sports()->syncWithoutDetaching([$sport->id]);

        return redirect()->route('student.sports.index')
            ->with('status', 'You joined '.$sport->name.'.');
    }

    public function leave(Sport $sport): RedirectResponse
    {
        $user = auth()->user();
        abort_unless((int) $sport->organization_id === (int) $user->organization_id, 403);

        $user->sports()->detach($sport->id);

        return redirect()->route('student.sports.index')
            ->with('status', 'You left '.$sport->name.'.');
    }
}
