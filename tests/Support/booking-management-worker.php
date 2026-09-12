<?php

// Only used by the opt-in MySQL concurrency test, against its disposable database.
require dirname(__DIR__, 2) . '/vendor/autoload.php';
$app = require dirname(__DIR__, 2) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$script, $database, $action, $bookingId, $actorId] = $argv;
if (! preg_match('/^booking_management_test_[a-f0-9]{16}$/', $database)) {
    throw new RuntimeException('Invalid test database.');
}
config([
    'database.default' => 'mysql',
    'database.connections.mysql.database' => $database,
    'database.connections.mysql.url' => null,
]);
Illuminate\Support\Facades\DB::purge('mysql');
Carbon\Carbon::setTestNow('2026-09-15 14:00:00');

$booking = App\Models\Booking::findOrFail($bookingId);
$actor = App\Models\User::findOrFail($actorId);
$thread = Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id;
echo "READY:$thread\n";
flush();

try {
    $service = app(App\Services\BookingService::class);
    match ($action) {
        'cancel' => $service->cancel($booking, $actor),
        'confirm' => $service->confirm($booking, $actor),
        default => throw new RuntimeException('Unknown test action.'),
    };
    echo "RESULT:success\n";
} catch (App\Exceptions\BookingException $e) {
    echo "RESULT:rejected\n";
}
