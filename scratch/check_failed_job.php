<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$failed = DB::table('failed_jobs')->latest('failed_at')->first();
if ($failed) {
    echo "ID: " . $failed->uuid . "\n";
    echo "Failed At: " . $failed->failed_at . "\n";
    echo "Exception (First 500 chars): \n" . substr($failed->exception, 0, 500) . "\n";
} else {
    echo "No failed jobs found.\n";
}
