<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Sport;
use App\Models\SportApplication;

$user = User::where('email', 'coke@gmail.com')->first();
if (!$user) {
    echo "User coke@gmail.com not found\n";
    exit;
}

echo "User: {$user->name} (ID: {$user->id})\n";

$sports = Sport::all();
foreach ($sports as $sport) {
    echo "Sport: {$sport->name} (ID: {$sport->id}), Instructor ID: {$sport->instructor_user_id}\n";
    $pending = SportApplication::where('sport_id', $sport->id)->where('status', 'pending')->count();
    echo "  Pending Applications: {$pending}\n";
}

$notifications = $user->notifications;
echo "Total Notifications for Coke: " . $notifications->count() . "\n";
foreach ($notifications as $n) {
    echo "  - Type: " . $n->type . " (Read: " . ($n->read_at ? 'Yes' : 'No') . ")\n";
}
