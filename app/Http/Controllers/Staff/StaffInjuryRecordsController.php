<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\InjuryRecord;
use App\Models\Sport;
use App\Models\User;
use App\Support\RosterAccess;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffInjuryRecordsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', InjuryRecord::class);

        $user = $request->user();
        $orgId = $user->organization_id;

        $athleteIds = RosterAccess::coachedStudentIds($user);

        $query = InjuryRecord::query()
            ->with(['athlete:id,name', 'sport:id,name'])
            ->where('organization_id', $orgId)
            ->orderByDesc('occurred_on')
            ->orderByDesc('id');

        if ($user->role !== 'admin') {
            $query->whereIn('athlete_user_id', $athleteIds);
        }

        $records = $query->paginate(25);

        $athletes = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->when($user->role !== 'admin', fn ($q) => $q->whereIn('id', $athleteIds))
            ->orderBy('name')
            ->limit(250)
            ->get(['id', 'name']);

        $sports = Sport::query()
            ->where('organization_id', $orgId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('staff.injury-records.index', compact('records', 'athletes', 'sports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', InjuryRecord::class);

        $user = $request->user();
        $orgId = $user->organization_id;

        $validated = $request->validate([
            'athlete_user_id' => ['required', 'integer', 'exists:users,id'],
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:open,monitoring,cleared'],
            'occurred_on' => ['required', 'date'],
        ]);

        $athlete = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->findOrFail($validated['athlete_user_id']);

        abort_unless(RosterAccess::actorMayViewAthlete($user, $athlete) || $user->role === 'admin', 403);

        $sport = null;
        if (! empty($validated['sport_id'])) {
            $sport = Sport::query()
                ->where('organization_id', $orgId)
                ->findOrFail((int) $validated['sport_id']);
        }

        $record = InjuryRecord::query()->create([
            'organization_id' => $orgId,
            'athlete_user_id' => $athlete->id,
            'reported_by_user_id' => $user->id,
            'sport_id' => $sport?->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'occurred_on' => CarbonImmutable::parse($validated['occurred_on'])->toDateString(),
        ]);

        activity()
            ->performedOn($record)
            ->causedBy($user)
            ->withProperties(['athlete_user_id' => $athlete->id, 'sport_id' => $sport?->id])
            ->log('injury_record_created');

        return redirect()->route('staff.injury_records.index')
            ->with('status', 'Injury record saved.');
    }
}
