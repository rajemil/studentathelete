<?php

namespace App\Http\Controllers;

use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SportTeamSuggestionController extends Controller
{
    public function index(Sport $sport): View
    {
        $this->authorize('view', $sport);

        return view('sports.team_suggestions', [
            'sport' => $sport,
            'result' => null,
        ]);
    }

    public function generate(Request $request, Sport $sport): View|RedirectResponse
    {
        $this->authorize('view', $sport);

        $validated = $request->validate([
            'mode' => ['required', 'in:strongest,balanced'],
            'team_count' => ['required', 'integer', 'min:2', 'max:12'],
            'team_size' => ['required', 'integer', 'min:2', 'max:30'],
        ]);

        $studentIds = $sport->students()
            ->where('role', 'student')
            ->where('users.organization_id', $request->user()->organization_id)
            ->pluck('users.id');

        $students = User::query()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        if ($students->isEmpty()) {
            return $this->redirectRoutePreservingModal($request, 'sports.show', $sport)
                ->with('status', 'Assign students to this sport first.');
        }

        $avgScores = PerformanceScore::query()
            ->where('sport_id', $sport->id)
            ->whereIn('user_id', $studentIds)
            ->select('user_id', DB::raw('avg(score) as avg_score'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id')
            ->map(fn ($row) => (float) $row->avg_score);

        $pool = $students->map(function (User $u) use ($avgScores) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avg_score' => round((float) ($avgScores[$u->id] ?? 0), 2),
            ];
        })->values();

        $teams = $validated['mode'] === 'strongest'
            ? $this->strongest($pool, (int) $validated['team_count'], (int) $validated['team_size'])
            : $this->balancedDraft($pool, (int) $validated['team_count'], (int) $validated['team_size']);

        return view('sports.team_suggestions', [
            'sport' => $sport,
            'result' => [
                'mode' => $validated['mode'],
                'team_count' => (int) $validated['team_count'],
                'team_size' => (int) $validated['team_size'],
                'teams' => $teams,
            ],
        ]);
    }

    private function strongest(Collection $pool, int $teamCount, int $teamSize): array
    {
        $sorted = $pool->sortByDesc('avg_score')->values();
        $chunks = $sorted->chunk($teamSize)->take($teamCount)->values();

        return $chunks->map(function (Collection $team, int $idx) {
            return [
                'name' => 'Team '.($idx + 1),
                'avg_score' => round($team->avg('avg_score'), 2),
                'members' => $team->values()->all(),
            ];
        })->all();
    }

    private function balancedDraft(Collection $pool, int $teamCount, int $teamSize): array
    {
        $sorted = $pool->sortByDesc('avg_score')->values();

        $teams = collect(range(1, $teamCount))->map(fn ($i) => collect())->values();

        $snake = true;
        foreach ($sorted as $i => $player) {
            $round = (int) floor($i / $teamCount);
            $pos = $i % $teamCount;
            $index = $round % 2 === 0 ? $pos : ($teamCount - 1 - $pos);

            if ($teams[$index]->count() < $teamSize) {
                $teams[$index]->push($player);
            }
        }

        return $teams->map(function (Collection $team, int $idx) {
            return [
                'name' => 'Team '.($idx + 1),
                'avg_score' => round($team->avg('avg_score'), 2),
                'members' => $team->values()->all(),
            ];
        })->all();
    }
}
