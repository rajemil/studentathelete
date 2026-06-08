<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$query = User::where('email', 'not like', 'ai.studentathlete001@gmail.com');
$count = $query->count();
$query->delete();

echo "Deleted $count dummy/seed users to clean up production dataset.\n";
