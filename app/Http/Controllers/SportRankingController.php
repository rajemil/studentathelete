<?php

namespace App\Http\Controllers;

use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SportRankingController extends Controller
{
    public function index(Sport $sport): View
    {
        $this->authorize('view', $sport);

        $studentIds = $sport->students()
            ->where('role', 'student')
            ->where('users.organization_id', auth()->user()->organization_id)
            ->pluck('users.id');

        $aggregates = PerformanceScore::query()
            ->where('sport_id', $sport->id)
            ->whereIn('user_id', $studentIds)
            ->select('user_id', DB::raw('avg(score) as avg_score'), DB::raw('count(*) as score_count'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $students = User::query()
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $ranked = $students
            ->map(function (User $u) use ($aggregates) {
                $row = $aggregates->get($u->id);

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'avg_score' => round((float) ($row?->avg_score ?? 0), 2),
                    'score_count' => (int) ($row?->score_count ?? 0),
                ];
            })
            ->sortBy([
                ['avg_score', 'desc'],
                ['score_count', 'desc'],
                ['name', 'asc'],
            ])
            ->values()
            ->map(function (array $row, int $idx) {
                $row['rank'] = $idx + 1;

                return $row;
            });

        $chart = [
            'labels' => $ranked->take(10)->pluck('name'),
            'values' => $ranked->take(10)->pluck('avg_score'),
        ];

        return view('sports.rankings', compact('sport', 'ranked', 'chart'));
    }
}
