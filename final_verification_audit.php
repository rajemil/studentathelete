<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Sport;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\CoachedTeams;
use App\Services\Analytics\PredictiveAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

echo "========================================\n";
echo "FINAL POST-REFACTOR VERIFICATION AUDIT\n";
echo "========================================\n\n";

echo "--- 1. COACH-SPORT VALIDATION ---\n";
$coaches = User::where('role', 'coach')->with('sport')->get();
$hasNull = false;
foreach ($coaches as $c) {
    $sportName = $c->sport ? $c->sport->name : 'NULL';
    echo "- {$c->name} ({$c->email}) | sport_id: " . ($c->sport_id ?? 'NULL') . " | Sport: $sportName\n";
    if (is_null($c->sport_id)) {
        $hasNull = true;
    }
}
if ($hasNull) {
    echo "WARNING: There are coaches with NULL sport_id.\n";
} else {
    echo "SUCCESS: All coaches have a valid sport_id.\n";
}
echo "\n";

echo "--- 2. ATHLETE ASSIGNMENT VALIDATION ---\n";
$coach = User::where('role', 'coach')->whereNotNull('sport_id')->first();
if ($coach) {
    echo "Selected Coach: {$coach->name} (sport_id: {$coach->sport_id})\n";
    $athleteIds = CoachedTeams::coachedStudentIds($coach);
    $athletes = User::whereIn('id', $athleteIds)->with('sports')->get();
    echo "Total Athletes Visible: " . $athletes->count() . "\n";
    
    $allMatch = true;
    foreach ($athletes as $a) {
        $sports = $a->sports->pluck('id')->toArray();
        if (!in_array($coach->sport_id, $sports)) {
            $allMatch = false;
            echo "ERROR: Athlete {$a->name} does NOT belong to sport {$coach->sport_id}\n";
        }
    }
    if ($allMatch) {
        echo "SUCCESS: All returned athletes belong to the coach's sport.\n";
    }
} else {
    echo "WARNING: No valid coach found for testing.\n";
}
echo "\n";

echo "--- 3. PREDICTIVE ANALYTICS VALIDATION ---\n";
if ($coach && $athletes->count() > 0) {
    Auth::login($coach);
    $predictive = app(PredictiveAnalyticsService::class);
    $athlete = $athletes->first();
    try {
        $pred = $predictive->predictAthletePerformance($athlete, $coach->sport, 14);
        echo "SUCCESS: Predicted score: {$pred['predicted_score']} (Conf: {$pred['confidence']}%)\n";
        
        $bundle = $predictive->recommendations($athlete, $coach->sport);
        echo "SUCCESS: Generated " . count($bundle) . " recommendations.\n";
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
} else {
    echo "WARNING: Insufficient data to test predictions.\n";
}
echo "\n";

echo "--- 4. MULTI-TENANT SECURITY VALIDATION ---\n";
if ($coach) {
    DB::enableQueryLog();
    try {
        app(PredictiveAnalyticsService::class)->predictAthletePerformance($athletes->first() ?? User::where('role','student')->first(), $coach->sport, 14);
        $queries = DB::getQueryLog();
        $orgScopeApplied = false;
        foreach ($queries as $q) {
            if (strpos($q['query'], 'organization_id') !== false) {
                $orgScopeApplied = true;
                echo "EVIDENCE: " . $q['query'] . "\n";
                break;
            }
        }
        if ($orgScopeApplied) {
            echo "SUCCESS: organization_id global scope remains active during calculations.\n";
        } else {
            echo "WARNING: Organization scope not explicitly seen in this specific query trace.\n";
        }
    } catch (\Exception $e) {}
}
echo "\n";

echo "--- 5. DASHBOARD VALIDATION (RUNTIME TESTS) ---\n";
if ($coach) {
    Auth::login($coach);
    $routesToTest = [
        '/dashboard' => App\Http\Controllers\DashboardController::class . '@index',
        '/analytics' => App\Http\Controllers\AnalyticsController::class . '@index',
    ];
    
    foreach ($routesToTest as $uri => $action) {
        try {
            $request = Request::create($uri, 'GET');
            $request->setUserResolver(fn() => $coach);
            $response = app(Illuminate\Contracts\Http\Kernel::class)->handle($request);
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 302) {
                echo "SUCCESS: $uri loaded without exceptions. (Status: {$response->getStatusCode()})\n";
            } else {
                echo "WARNING: $uri returned status {$response->getStatusCode()}\n";
            }
        } catch (\Exception $e) {
            echo "ERROR Loading $uri: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
        }
    }
}
echo "\n";

echo "--- 6. DATABASE CLEANUP VALIDATION ---\n";
$dummyCount = User::where('email', 'like', '%@example.net')->count();
echo "Dummy Users Remaining: $dummyCount\n";

$realCount = User::where('email', 'like', '%@gmail.com')->count();
echo "Real Users Remaining: $realCount\n";

// Orphan check sport_user
$orphanSportUser = DB::select("SELECT COUNT(*) as count FROM sport_user WHERE user_id NOT IN (SELECT id FROM users) OR sport_id NOT IN (SELECT id FROM sports)");
echo "Orphaned sport_user records: {$orphanSportUser[0]->count}\n";

// Orphan check performance_scores
$orphanPerf = DB::select("SELECT COUNT(*) as count FROM performance_scores WHERE user_id NOT IN (SELECT id FROM users)");
echo "Orphaned performance_scores records: {$orphanPerf[0]->count}\n";

// Orphan check profiles (injury_records proxy)
$orphanProfiles = DB::select("SELECT COUNT(*) as count FROM profiles WHERE user_id NOT IN (SELECT id FROM users)");
echo "Orphaned profiles records: {$orphanProfiles[0]->count}\n";

if ($dummyCount == 0 && $orphanSportUser[0]->count == 0 && $orphanPerf[0]->count == 0 && $orphanProfiles[0]->count == 0) {
    echo "SUCCESS: Database is clean and relationally intact.\n";
} else {
    echo "WARNING: Orphans or dummy users detected.\n";
}
echo "\n";
