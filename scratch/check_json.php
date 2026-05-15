<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

$user = User::where('email', 'coke@gmail.com')->first();
$items = $user->notifications()
    ->latest()
    ->limit(15)
    ->get()
    ->map(fn ($n) => [
        'id' => $n->id,
        'read_at' => optional($n->read_at)?->toISOString(),
        'created_at' => optional($n->created_at)?->toISOString(),
        'data' => $n->data,
    ]);

echo json_encode([
    'unread_count' => (int) $user->unreadNotifications()->count(),
    'notifications' => $items,
], JSON_PRETTY_PRINT);
