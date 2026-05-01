<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Support\CoachedTeams;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Sport::class);

        $user = auth()->user();

        $sportsQuery = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->orderBy('name');

        $studentsQuery = User::query()
            ->where('organization_id', $user->organization_id)
            ->where('role', 'student')
            ->orderBy('name')
            ->limit(500);

        if (in_array($user->role, ['coach', 'instructor'], true)) {
            $sportIds = Team::query()
                ->whereIn('id', CoachedTeams::teamIds($user))
                ->pluck('sport_id')
                ->unique()
                ->filter();

            if ($sportIds->isEmpty()) {
                $sportsQuery->whereRaw('1 = 0');
            } else {
                $sportsQuery->whereIn('id', $sportIds);
            }

            $studentIds = CoachedTeams::coachedStudentIds($user);
            if ($studentIds->isEmpty()) {
                $studentsQuery->whereRaw('1 = 0');
            } else {
                $studentsQuery->whereIn('id', $studentIds);
            }
        }

        $sports = $sportsQuery->get(['id', 'name']);
        $students = $studentsQuery->get(['id', 'name', 'email']);

        return view('analytics.index', compact('sports', 'students'));
    }
}
