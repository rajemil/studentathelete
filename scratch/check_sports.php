<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Support\CoachedTeams;

$user = User::where('email', 'coke@gmail.com')->first();
$assignedSportIds = $user->sports()->pluck('sports.id');
$teamIds = CoachedTeams::teamIds($user);
$teamSportIds = \App\Models\Team::whereIn('id', $teamIds)->pluck('sport_id');
$instructorSportIds = \App\Models\Sport::where('instructor_user_id', $user->id)->pluck('id');

echo "Assigned Sport IDs (Pivot): " . $assignedSportIds->implode(', ') . "\n";
echo "Team IDs Coached: " . $teamIds->implode(', ') . "\n";
echo "Sport IDs from Teams: " . $teamSportIds->implode(', ') . "\n";
echo "Instructor Sport IDs: " . $instructorSportIds->implode(', ') . "\n";

$all = $assignedSportIds->merge($teamSportIds)->merge($instructorSportIds)->unique();
echo "Total Unique Sport IDs for Coke: " . $all->implode(', ') . "\n";
