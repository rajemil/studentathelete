<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$coaches = \App\Models\User::where('role', 'coach')->get();
foreach ($coaches as $u) {
    echo "Coach: {$u->name} (id={$u->id}) sport_id={$u->sport_id} org_id={$u->organization_id}" . PHP_EOL;
    $teamIds = \App\Support\CoachedTeams::teamIds($u);
    echo "  CoachedTeams::teamIds => [{$teamIds->implode(',')}]" . PHP_EOL;

    // Check if there are teams with this sport
    if ($u->sport_id) {
        $teamsForSport = \App\Models\Team::where('sport_id', $u->sport_id)->get(['id','name','sport_id','primary_coach_id','organization_id']);
        echo "  Teams for sport_id={$u->sport_id}: " . $teamsForSport->count() . PHP_EOL;
        foreach ($teamsForSport as $t) {
            echo "    Team: {$t->name} (id={$t->id}) primary_coach_id={$t->primary_coach_id} org_id={$t->organization_id}" . PHP_EOL;
        }
    }
}

// Also check what sports exist
$sports = \App\Models\Sport::all(['id', 'name', 'organization_id']);
echo PHP_EOL . "All Sports:" . PHP_EOL;
foreach ($sports as $s) {
    echo "  id={$s->id} name={$s->name} org_id={$s->organization_id}" . PHP_EOL;
}

// Check teams table
$teams = \App\Models\Team::all(['id', 'name', 'sport_id', 'primary_coach_id', 'organization_id']);
echo PHP_EOL . "All Teams:" . PHP_EOL;
foreach ($teams as $t) {
    echo "  id={$t->id} name={$t->name} sport_id={$t->sport_id} primary_coach_id={$t->primary_coach_id} org_id={$t->organization_id}" . PHP_EOL;
}
