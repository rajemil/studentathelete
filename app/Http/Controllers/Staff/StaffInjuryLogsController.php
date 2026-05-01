<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Support\CoachedTeams;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaffInjuryLogsController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $teamIds = CoachedTeams::teamIds($user);

        $athleteIds = DB::table('team_memberships')
            ->whereIn('team_id', $teamIds)
            ->distinct('user_id')
            ->pluck('user_id');

        $athletes = User::query()
            ->where('organization_id', $user->organization_id)
            ->whereIn('id', $athleteIds)
            ->where('role', 'student')
            ->with('profile')
            ->orderBy('name')
            ->get();

        $teamsBySport = Team::query()
            ->where('organization_id', $user->organization_id)
            ->whereIn('id', $teamIds)
            ->with('sport:id,name')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Team $t) => $t->sport?->name ?? 'General');

        return view('staff.injury-logs', compact('athletes', 'teamsBySport'));
    }
}
