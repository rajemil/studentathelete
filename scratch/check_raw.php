<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$n = DB::table('notifications')->latest()->first();
if ($n) {
    echo "Notification ID: " . $n->id . "\n";
    echo "Raw Data: " . $n->data . "\n";
} else {
    echo "No notifications found.\n";
}
