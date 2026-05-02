<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoachAssignment;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $orgId = auth()->user()->organization_id;

        $users = User::query()
            ->where('organization_id', $orgId)
            ->whereIn('role', ['admin', 'coach', 'instructor'])
            ->with([
                'sports',
                'profile',
                'coachAssignments.team.sport',
                'primaryCoachedTeams.sport',
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        $sports = Sport::query()
            ->where('organization_id', $orgId)
            ->with('instructor')
            ->with(['teams' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        // Used to disable sports that are already assigned to some faculty.
        $sportFacultyAssignments = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->where('users.organization_id', $orgId)
            ->whereIn('users.role', ['coach', 'instructor'])
            ->select('sport_user.sport_id', 'users.id as user_id', 'users.name', 'users.email', 'users.role')
            ->orderBy('users.created_at')
            ->get()
            ->groupBy('sport_id')
            ->map(fn ($rows) => $rows->first())
            ->all();

        return view('admin.users.index', compact('users', 'sports', 'sportFacultyAssignments'));
    }

    public function create(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,coach,instructor'],
            'password' => ['nullable', 'string', 'min:8'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'address' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'field_expertise' => ['nullable', 'string', 'max:255'],
            'achievements' => ['nullable', 'string'],
            'coaching_experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
            'sport_ids' => ['sometimes', 'array'],
            'sport_ids.*' => ['integer'],
        ]);

        $password = $validated['password'] ?: 'password';

        $user = User::query()->create([
            'organization_id' => $orgId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($password),
            'email_verified_at' => CarbonImmutable::now(),
        ]);

        $birthdate = isset($validated['birthdate']) ? CarbonImmutable::parse($validated['birthdate']) : null;
        // profiles.age is an unsignedSmallInteger → must be an integer
        $computedAge = $birthdate ? (int) $birthdate->age : null;

        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'birthdate' => $birthdate?->toDateString(),
            'age' => $computedAge,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'profession' => $validated['profession'] ?? null,
            'field_expertise' => $validated['field_expertise'] ?? null,
            'achievements' => $validated['achievements'] ?? null,
            'coaching_experience_years' => $validated['coaching_experience_years'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('faculty-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $allowedSportIds = Sport::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $desiredSportIds = collect($validated['sport_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $id) => in_array($id, $allowedSportIds, true))
            ->unique()
            ->values();

        // Enforce: one faculty (coach/instructor) per sport.
        $alreadyAssignedSportIds = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->where('users.organization_id', $orgId)
            ->whereIn('users.role', ['coach', 'instructor'])
            ->whereIn('sport_user.sport_id', $desiredSportIds->all())
            ->distinct()
            ->pluck('sport_user.sport_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        if (! empty($alreadyAssignedSportIds)) {
            return back()->withErrors([
                'sport_ids' => 'One or more selected sports are already assigned to another faculty member.',
            ])->withInput();
        }

        // Persist sport assignment (even if the sport currently has no teams).
        $user->sports()->syncWithoutDetaching($desiredSportIds->all());

        $teamIds = Team::query()
            ->where('organization_id', $orgId)
            ->whereIn('sport_id', $desiredSportIds)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($teamIds as $teamId) {
            CoachAssignment::query()->firstOrCreate([
                'coach_id' => $user->id,
                'team_id' => $teamId,
                'assignment_role' => 'coach',
            ], [
                'starts_on' => CarbonImmutable::now()->toDateString(),
                'ends_on' => null,
            ]);
        }

        if ($user->role === 'instructor') {
            Sport::query()
                ->where('organization_id', $orgId)
                ->whereIn('id', $desiredSportIds)
                ->whereNull('instructor_user_id')
                ->update(['instructor_user_id' => $user->id]);
        }

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties(['role' => $validated['role']])
            ->log('faculty_created');

        return redirect()->route('admin.users.index')->with('status', 'Faculty account created.');
    }

    public function edit(User $user): View
    {
        $this->authorize('view', $user);

        $orgId = auth()->user()->organization_id;

        $teams = Team::query()
            ->where('organization_id', $orgId)
            ->with('sport')
            ->orderBy('name')
            ->get();

        $assignedTeamIds = CoachAssignment::query()
            ->where('coach_id', $user->id)
            ->where('assignment_role', 'coach')
            ->whereIn('team_id', $teams->pluck('id'))
            ->pluck('team_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        return view('admin.users.edit', compact('user', 'teams', 'assignedTeamIds'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('updateRole', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'string', 'in:admin,coach,instructor'],
            'sport_ids' => ['sometimes', 'array'],
            'sport_ids.*' => ['integer'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'address' => ['nullable', 'string', 'max:255'],
            'profession' => ['nullable', 'string', 'max:255'],
            'field_expertise' => ['nullable', 'string', 'max:255'],
            'achievements' => ['nullable', 'string'],
            'coaching_experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $admins = User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('role', 'admin')
                ->count();

            if ($admins <= 1) {
                return back()->withErrors(['role' => 'You cannot demote the only administrator.']);
            }
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        $birthdate = isset($validated['birthdate']) ? CarbonImmutable::parse($validated['birthdate']) : null;
        // profiles.age is an unsignedSmallInteger → must be an integer
        $computedAge = $birthdate ? (int) $birthdate->age : null;

        $profile = $user->profile ?: Profile::query()->create(['user_id' => $user->id]);
        $profile->fill([
            'birthdate' => $birthdate?->toDateString(),
            'age' => $computedAge,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'profession' => $validated['profession'] ?? null,
            'field_expertise' => $validated['field_expertise'] ?? null,
            'achievements' => $validated['achievements'] ?? null,
            'coaching_experience_years' => $validated['coaching_experience_years'] ?? null,
        ])->save();

        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $path = $request->file('photo')->store('faculty-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $orgId = $request->user()->organization_id;
        $allowedSportIds = Sport::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $desiredSportIds = collect($validated['sport_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $id) => in_array($id, $allowedSportIds, true))
            ->unique()
            ->values();

        // Enforce: one faculty (coach/instructor) per sport (allow keeping already-owned sports).
        $alreadyAssignedToOther = DB::table('sport_user')
            ->join('users', 'sport_user.user_id', '=', 'users.id')
            ->where('users.organization_id', $orgId)
            ->whereIn('users.role', ['coach', 'instructor'])
            ->where('sport_user.user_id', '!=', $user->id)
            ->whereIn('sport_user.sport_id', $desiredSportIds->all())
            ->exists();

        if ($alreadyAssignedToOther) {
            return back()->withErrors([
                'sport_ids' => 'One or more selected sports are already assigned to another faculty member.',
            ])->withInput();
        }

        // Keep the faculty's sport assignments in sync.
        $user->sports()->sync($desiredSportIds->all());

        $allowedTeamIds = Team::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $desiredTeamIds = Team::query()
            ->where('organization_id', $orgId)
            ->whereIn('sport_id', $desiredSportIds)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        // Sync "coach" assignments only (keeps primary coach and other roles intact).
        CoachAssignment::query()
            ->where('coach_id', $user->id)
            ->where('assignment_role', 'coach')
            ->whereIn('team_id', $allowedTeamIds)
            ->whereNotIn('team_id', $desiredTeamIds)
            ->delete();

        $existing = CoachAssignment::query()
            ->where('coach_id', $user->id)
            ->where('assignment_role', 'coach')
            ->whereIn('team_id', $allowedTeamIds)
            ->pluck('team_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $toAdd = collect($desiredTeamIds)->filter(fn (int $id) => ! in_array($id, $existing, true));
        foreach ($toAdd as $teamId) {
            CoachAssignment::query()->firstOrCreate([
                'coach_id' => $user->id,
                'team_id' => $teamId,
                'assignment_role' => 'coach',
            ], [
                'starts_on' => CarbonImmutable::now()->toDateString(),
                'ends_on' => null,
            ]);
        }

        // Instructor assignment exclusivity (one instructor per sport)
        if ($validated['role'] === 'instructor') {
            Sport::query()
                ->where('organization_id', $orgId)
                ->where('instructor_user_id', $user->id)
                ->whereNotIn('id', $desiredSportIds->all())
                ->update(['instructor_user_id' => null]);

            Sport::query()
                ->where('organization_id', $orgId)
                ->whereIn('id', $desiredSportIds->all())
                ->where(function ($q) use ($user) {
                    $q->whereNull('instructor_user_id')->orWhere('instructor_user_id', $user->id);
                })
                ->update(['instructor_user_id' => $user->id]);
        }

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties([
                'new_role' => $validated['role'],
                'sport_ids' => $desiredSportIds->all(),
            ])
            ->log('faculty_updated');

        return redirect()->route('admin.users.index')
            ->with('status', 'Faculty updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Prevent deleting the last admin in the organization.
        if ($user->role === 'admin') {
            $admins = User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('role', 'admin')
                ->count();

            if ($admins <= 1) {
                return back()->withErrors(['delete' => 'You cannot delete the only administrator.']);
            }
        }

        if ($user->profile?->photo_path) {
            Storage::disk('public')->delete($user->profile->photo_path);
        }

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('faculty_deleted');

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('status', 'Faculty deleted.');
    }
}
