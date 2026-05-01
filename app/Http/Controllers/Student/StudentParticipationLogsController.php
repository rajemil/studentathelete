<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ParticipationLog;
use App\Models\Sport;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentParticipationLogsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ParticipationLog::class);

        $user = $request->user();

        $logs = ParticipationLog::query()
            ->with('sport:id,name')
            ->where('organization_id', $user->organization_id)
            ->where('user_id', $user->id)
            ->orderByDesc('logged_on')
            ->orderByDesc('id')
            ->paginate(25);

        $sports = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('student.participation-logs.index', compact('logs', 'sports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ParticipationLog::class);

        $user = $request->user();

        $validated = $request->validate([
            'sport_id' => ['nullable', 'integer', 'exists:sports,id'],
            'activity_type' => ['required', 'in:training,competition,recovery,other'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'logged_on' => ['required', 'date'],
        ]);

        $sportId = null;
        if (! empty($validated['sport_id'])) {
            $sportId = (int) Sport::query()
                ->where('organization_id', $user->organization_id)
                ->whereKey((int) $validated['sport_id'])
                ->value('id');
        }

        $log = ParticipationLog::query()->create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'sport_id' => $sportId,
            'activity_type' => $validated['activity_type'],
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'logged_on' => CarbonImmutable::parse($validated['logged_on'])->toDateString(),
        ]);

        activity()
            ->performedOn($log)
            ->causedBy($user)
            ->withProperties(['sport_id' => $sportId, 'activity_type' => $validated['activity_type']])
            ->log('participation_log_created');

        return redirect()->route('student.participation_logs.index')
            ->with('status', 'Activity logged.');
    }
}
