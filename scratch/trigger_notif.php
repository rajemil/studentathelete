<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SportApplication;
use App\Notifications\SportApplicationSubmitted;

$user = User::where('email', 'coke@gmail.com')->first();
if (!$user) {
    echo "User coke@gmail.com not found\n";
    exit;
}

$pendingApplications = SportApplication::where('status', 'pending')->get();
echo "Found " . $pendingApplications->count() . " pending applications.\n";

foreach ($pendingApplications as $app) {
    echo "Notifying coke about app ID: {$app->id}\n";
    $user->notify(new SportApplicationSubmitted($app));
}

echo "Done. Coke unread count: " . $user->unreadNotifications()->count() . "\n";
foreach ($user->unreadNotifications as $n) {
    echo "Notification ID: " . $n->id . " Data: " . json_encode($n->data) . "\n";
}
