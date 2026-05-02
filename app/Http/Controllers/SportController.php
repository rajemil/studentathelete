<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\CoachedTeams;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Sport::class, 'sport');
    }

    public function index(): View
    {
        $user = auth()->user();

        $query = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->withCount('students')
            ->orderBy('name');

        if (in_array($user->role, ['coach', 'instructor'], true)) {
            $sportIds = Team::query()
                ->whereIn('id', CoachedTeams::teamIds($user))
                ->pluck('sport_id')
                ->unique()
                ->filter();

            if ($sportIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('id', $sportIds);
            }
        }

        if ($user->role === 'student') {
            $query->whereHas('students', fn ($q) => $q->where('users.id', $user->id));
        }

        $sports = $query->paginate(12);

        return view('sports.index', compact('sports'));
    }

    public function create(): View
    {
        return view('sports.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('sports', 'name')->where('organization_id', $user->organization_id)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('sports', 'slug')->where('organization_id', $user->organization_id)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $sport = Sport::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        activity()
            ->performedOn($sport)
            ->causedBy($user)
            ->log('sport_created');

        return $this->redirectRoutePreservingModal($request, 'sports.show', $sport)
            ->with('status', 'Sport created.');
    }

    public function show(Sport $sport): View
    {
        $sport->loadCount('students');

        $students = $sport->students()
            ->where('role', 'student')
            ->where('users.organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->paginate(12, ['users.*'], 'students_page');

        $availableStudents = User::query()
            ->where('role', 'student')
            ->where('organization_id', auth()->user()->organization_id)
            ->whereDoesntHave('sports', fn ($q) => $q->where('sports.id', $sport->id))
            ->orderBy('name')
            ->limit(250)
            ->get(['id', 'name', 'email']);

        return view('sports.show', compact('sport', 'students', 'availableStudents'));
    }

    public function edit(Sport $sport): View
    {
        return view('sports.edit', compact('sport'));
    }

    public function update(Request $request, Sport $sport): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('sports', 'name')->where('organization_id', $user->organization_id)->ignore($sport->id)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('sports', 'slug')->where('organization_id', $user->organization_id)->ignore($sport->id)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $sport->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        activity()
            ->performedOn($sport)
            ->causedBy($user)
            ->log('sport_updated');

        return $this->redirectRoutePreservingModal($request, 'sports.show', $sport)
            ->with('status', 'Sport updated.');
    }

    public function destroy(Sport $sport): RedirectResponse
    {
        $sport->delete();

        activity()
            ->performedOn($sport)
            ->causedBy(auth()->user())
            ->log('sport_deleted');

        return redirect()->route('sports.index')
            ->with('status', 'Sport deleted.');
    }
}
