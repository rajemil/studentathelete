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
        return view('sports.team_suggestions', [
            'sport' => $sport,
            'result' => null,
        ]);
    }

    public function generate(Request $request, Sport $sport): View|RedirectResponse
    {
        $validated = $request->validate([
            'mode' => ['required', 'in:strongest,balanced'],
            'team_count' => ['required', 'integer', 'min:2', 'max:12'],
            'team_size' => ['required', 'integer', 'min:2', 'max:30'],
        ]);

        $studentIds = $sport->students()
            ->where('role', 'student')
            ->pluck('users.id');

        $students = User::query()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        if ($students->isEmpty()) {
            return redirect()->route('sports.show', $sport)
                ->with('status', 'Assign students to this sport first.');
        }

        $avgScores = PerformanceScore::query()
            ->where('sport_id', $sport->id)
            ->whereIn('user_id', $studentIds)
            ->select('user_id', DB::raw('avg(score) as avg_score'))
            ->groupBy('user_id')
            ->pluck('avg_score', 'user_id')
            ->map(fn ($v) => (float) $v);

        $ranked = $students
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'score' => round((float) ($avgScores[$u->id] ?? 0), 2),
            ])
            ->sortBy([
                ['score', 'desc'],
                ['name', 'asc'],
            ])
            ->values();

        $needed = $validated['team_count'] * $validated['team_size'];
        $pool = $ranked->take($needed)->values();

        $teams = $validated['mode'] === 'strongest'
            ? $this->generateStrongest($pool, (int) $validated['team_count'], (int) $validated['team_size'])
            : $this->generateBalancedSnake($pool, (int) $validated['team_count'], (int) $validated['team_size']);

        $scoredTeams = $teams->map(function (Collection $team, int $idx) {
            $avg = $team->avg('score') ?? 0;
            $sum = $team->sum('score');

            return [
                'name' => 'Team '.($idx + 1),
                'avg_score' => round((float) $avg, 2),
                'sum_score' => round((float) $sum, 2),
                'members' => $team->values(),
            ];
        })->values();

        $winProbabilities = $this->pairwiseWinProbabilities($scoredTeams);

        $result = [
            'mode' => $validated['mode'],
            'team_count' => (int) $validated['team_count'],
            'team_size' => (int) $validated['team_size'],
            'pool_count' => $pool->count(),
            'teams' => $scoredTeams,
            'win_probabilities' => $winProbabilities,
        ];

        return view('sports.team_suggestions', compact('sport', 'result'));
    }

    private function generateStrongest(Collection $pool, int $teamCount, int $teamSize): Collection
    {
        $teams = collect(range(1, $teamCount))->map(fn () => collect());
        $idx = 0;

        for ($t = 0; $t < $teamCount; $t++) {
            for ($i = 0; $i < $teamSize; $i++) {
                if (! isset($pool[$idx])) break;
                $teams[$t]->push($pool[$idx]);
                $idx++;
            }
        }

        return $teams;
    }

    private function generateBalancedSnake(Collection $pool, int $teamCount, int $teamSize): Collection
    {
        $teams = collect(range(1, $teamCount))->map(fn () => collect());
        $direction = 1;
        $teamIndex = 0;

        foreach ($pool as $player) {
            if ($teams[$teamIndex]->count() < $teamSize) {
                $teams[$teamIndex]->push($player);
            }

            // advance snake
            $next = $teamIndex + $direction;
            if ($next >= $teamCount || $next < 0) {
                $direction *= -1;
                $teamIndex += $direction;
            } else {
                $teamIndex = $next;
            }

            // stop early if all full
            if ($teams->every(fn (Collection $t) => $t->count() >= $teamSize)) {
                break;
            }
        }

        return $teams;
    }

    /**
     * Simple win probability based on score difference using logistic curve.
     */
    private function winProbability(float $aAvg, float $bAvg): float
    {
        $diff = $aAvg - $bAvg;
        $scale = 5.0; // larger => flatter curve
        $p = 1.0 / (1.0 + exp(-$diff / $scale));
        return round($p * 100, 1);
    }

    private function pairwiseWinProbabilities(Collection $scoredTeams): array
    {
        $matrix = [];
        foreach ($scoredTeams as $i => $a) {
            foreach ($scoredTeams as $j => $b) {
                if ($i === $j) continue;
                $matrix[$i][$j] = $this->winProbability((float) $a['avg_score'], (float) $b['avg_score']);
            }
        }
        return $matrix;
    }
}
