<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Sport;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\PerformanceScore;
use App\Services\Analytics\PredictiveAnalyticsService;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\CoachedTeams;
use Illuminate\Support\Facades\Hash;

echo "--- 0. TEST DATA GENERATION ---\n";
// Create org
$org = Organization::firstOrCreate(
    ['id' => 1],
    ['name' => 'Audit University', 'domain' => 'audit.edu']
);

// Create sport
$sport = Sport::firstOrCreate(
    ['name' => 'Basketball'],
    ['organization_id' => $org->id, 'description' => 'Men\'s Basketball', 'season' => 'Winter', 'is_active' => true]
);

// Create coach
$coach = User::firstOrCreate(
    ['email' => 'coach.audit@audit.edu'],
    [
        'name' => 'Coach Carter',
        'password' => Hash::make('password'),
        'role' => 'coach',
        'organization_id' => $org->id,
        'sport_id' => $sport->id,
        'approval_status' => 'approved',
        'email_verified_at' => now(),
    ]
);
// Ensure sport_id is set if it was already created without it
if ($coach->sport_id !== $sport->id) {
    $coach->update(['sport_id' => $sport->id]);
}

// Create 3 students
$students = [];
for ($i = 1; $i <= 3; $i++) {
    $student = User::firstOrCreate(
        ['email' => "student.audit.{$i}@audit.edu"],
        [
            'name' => "Student Athlete {$i}",
            'password' => Hash::make('password'),
            'role' => 'student',
            'organization_id' => $org->id,
            'approval_status' => 'approved',
            'email_verified_at' => now(),
        ]
    );
    Profile::firstOrCreate(
        ['user_id' => $student->id],
        [
            'birthdate' => '2005-01-01',
            'gender' => 'male',
            'injury_risk' => 'low',
            'fatigue_score' => 20,
        ]
    );
    // Sync sport
    $student->sports()->syncWithoutDetaching([$sport->id]);
    
    // Add performance score
    PerformanceScore::create([
        'user_id' => $student->id,
        'sport_id' => $sport->id,
        'score' => rand(70, 95),
        'scored_on' => now()->subDays(rand(1, 10)),
        'notes' => 'Audit performance',
    ]);
    
    $students[] = $student;
}
echo "Generated Org, Sport, Coach, and 3 Students.\n\n";

echo "--- 1. COACH AUDIT ---\n";
$coaches = User::where('role', 'coach')->with('sport')->get();
if ($coaches->isEmpty()) {
    echo "NO COACHES FOUND.\n";
} else {
    foreach ($coaches as $c) {
        $sportName = $c->sport ? $c->sport->name : 'NULL / INVALID';
        echo "Coach: {$c->name} ({$c->email}) | sport_id: " . ($c->sport_id ?? 'NULL') . " | Sport: {$sportName}\n";
    }
}
echo "\n";

echo "--- 6. SCHEMA REVIEW (STUDENT TO SPORT) ---\n";
$studentCount = User::where('role', 'student')->count();
$pivotCount = DB::table('sport_user')->count();
echo "Total Students: $studentCount\n";
echo "Total sport_user pivot entries: $pivotCount\n";
$firstStudent = User::where('role', 'student')->has('sports')->with('sports')->first();
if ($firstStudent) {
    echo "Sample Student '{$firstStudent->name}' sports: " . $firstStudent->sports->pluck('name')->implode(', ') . "\n";
} else {
    echo "No students with sports found.\n";
}
echo "\n";

echo "--- 2. LIVE TEST (CREATE TEST STUDENT) ---\n";
echo "Using Coach: {$coach->name} from Org: {$org->name}\n";
$testStudent = User::create([
    'organization_id' => $org->id,
    'name' => 'Audit Live Test Student',
    'email' => 'audit.live.test.' . time() . '@example.com',
    'password' => bcrypt('password'),
    'role' => 'student',
    'email_verified_at' => now(),
    'approval_status' => 'approved',
]);
Profile::create([
    'user_id' => $testStudent->id,
    'birthdate' => '2005-01-01',
    'gender' => 'male',
    'injury_risk' => 'low',
    'fatigue_score' => 20,
]);
$testStudent->sports()->attach($coach->sport_id);
echo "Created student '{$testStudent->name}' and attached to sport_id {$coach->sport_id}.\n";

$dashboardStudents = CoachedTeams::coachedStudentIds($coach);
$appearsInDashboard = $dashboardStudents->contains($testStudent->id);
echo "Student appears in Coach's Dashboard (CoachedTeams::coachedStudentIds): " . ($appearsInDashboard ? 'YES' : 'NO') . "\n";
echo "\n";

echo "--- 4. TENANCY SECURITY AUDIT ---\n";
Auth::login($coach);
DB::enableQueryLog();
$predictiveService = app(PredictiveAnalyticsService::class);
$prediction = $predictiveService->predictAthletePerformance($testStudent, $coach->sport, 14);
$queries = DB::getQueryLog();
echo "Predicted score for test student: " . json_encode($prediction) . "\n";

$hasOrgScope = false;
foreach ($queries as $q) {
    if (strpos($q['query'], 'organization_id') !== false) {
        $hasOrgScope = true;
        echo "Tenant Query Trace: " . $q['query'] . "\n";
        break;
    }
}
echo "Organization Scope applied in queries: " . ($hasOrgScope ? "YES" : "NO") . "\n";
echo "\n";

echo "--- 5. API PREDICTIVE ENDPOINTS TEST ---\n";
foreach ($students as $athlete) {
    try {
        $pred = $predictiveService->predictAthletePerformance($athlete, $sport, 14);
        echo "Athlete: {$athlete->name} | Sport: {$sport->name} | Predicted Score: {$pred['predicted_score']} (Conf: {$pred['confidence']}%)\n";
    } catch (\Exception $e) {
        echo "Athlete: {$athlete->name} | ERROR: " . $e->getMessage() . "\n";
    }
}
echo "\nCleaned up live test student.\n";
$testStudent->delete();

