<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Team;
use App\Support\CoachedTeams;
use App\Support\EventTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffEventController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Event::class);

        $user = $request->user();
        $orgId = $user->organization_id;
        $teamIds = CoachedTeams::teamIds($user);

        $query = Event::query()
            ->with(['team:id,name,sport_id', 'team.sport:id,name', 'creator:id,name'])
            ->withCount('participants')
            ->where(function ($q) use ($orgId) {
                // Since Event doesn't have organization_id directly, we filter by relation organization_id
                $q->whereHas('team', fn ($t) => $t->where('organization_id', $orgId))
                    ->orWhereHas('sport', fn ($s) => $s->where('organization_id', $orgId))
                    ->orWhereHas('creator', fn ($u) => $u->where('organization_id', $orgId));
            });

        // If not admin, restrict to coached teams
        if ($user->role !== 'admin') {
            $query->whereIn('team_id', $teamIds);
        }

        // Apply filters
        if ($request->filled('team_id')) {
            $query->where('team_id', $request->integer('team_id'));
        }
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->string('event_type'));
        }

        $events = $query->orderBy('starts_at')
            ->paginate(15)
            ->withQueryString();

        $teams = Team::query()
            ->where('organization_id', $orgId)
            ->when($user->role !== 'admin', fn ($q) => $q->whereIn('id', $teamIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('staff.events.index', compact('events', 'teams'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Event::class);

        $user = $request->user();
        $orgId = $user->organization_id;
        $teamIds = CoachedTeams::teamIds($user);

        $teams = Team::query()
            ->where('organization_id', $orgId)
            ->when($user->role !== 'admin', fn ($q) => $q->whereIn('id', $teamIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('staff.events.create', compact('teams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Event::class);

        $user = $request->user();
        $orgId = $user->organization_id;
        $teamIds = CoachedTeams::teamIds($user);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'in:'.implode(',', EventTypes::values())],
        ]);

        // Verify the team belongs to the current user's coached teams (or organization if admin)
        $teamQuery = Team::where('organization_id', $orgId);
        if ($user->role !== 'admin') {
            $teamQuery->whereIn('id', $teamIds);
        }
        $team = $teamQuery->findOrFail($validated['team_id']);

        $validated['sport_id'] = $team->sport_id;
        $validated['created_by'] = $user->id;

        $event = Event::create($validated);

        // Sync participants (students in team + the coach/creator)
        $studentIds = $team->students()->pluck('users.id')->toArray();
        $participants = [];
        foreach ($studentIds as $id) {
            $participants[$id] = ['participant_role' => 'student'];
        }
        $participants[$user->id] = ['participant_role' => 'coach'];

        $event->participants()->sync($participants);

        return redirect()->route('staff.events.index')
            ->with('status', 'Event scheduled successfully.');
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $user = auth()->user();
        $orgId = $user->organization_id;
        $teamIds = CoachedTeams::teamIds($user);

        $teams = Team::query()
            ->where('organization_id', $orgId)
            ->when($user->role !== 'admin', fn ($q) => $q->whereIn('id', $teamIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('staff.events.edit', compact('event', 'teams'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $user = $request->user();
        $orgId = $user->organization_id;
        $teamIds = CoachedTeams::teamIds($user);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:255'],
            'event_type' => ['required', 'string', 'in:'.implode(',', EventTypes::values())],
        ]);

        $teamQuery = Team::where('organization_id', $orgId);
        if ($user->role !== 'admin') {
            $teamQuery->whereIn('id', $teamIds);
        }
        $team = $teamQuery->findOrFail($validated['team_id']);

        $originalTeamId = $event->team_id;

        $validated['sport_id'] = $team->sport_id;
        $event->update($validated);

        // If team changed or we want to update participants, resync them
        if ($originalTeamId !== (int) $validated['team_id']) {
            $studentIds = $team->students()->pluck('users.id')->toArray();
            $participants = [];
            foreach ($studentIds as $id) {
                $participants[$id] = ['participant_role' => 'student'];
            }
            $participants[$user->id] = ['participant_role' => 'coach'];
            $event->participants()->sync($participants);
        }

        return redirect()->route('staff.events.index')
            ->with('status', 'Event updated successfully.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('staff.events.index')
            ->with('status', 'Event cancelled successfully.');
    }
}
