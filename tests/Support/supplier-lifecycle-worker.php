<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
[$script, $database, $hotelId, $roomId, $boardId] = $argv;
if (! preg_match('/^supplier_lifecycle_test_[a-f0-9]{16}$/', $database)) {
    throw new RuntimeException('Invalid test database.');
}
config([
    'database.default' => 'mysql',
    'database.connections.mysql.database' => $database,
    'database.connections.mysql.url' => null,
]);
Illuminate\Support\Facades\DB::purge('mysql');
Carbon\Carbon::setTestNow('2026-10-01 12:00:00');
$thread = Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id;
echo "READY:$thread\n";
flush();
try {
    app(App\Services\BookingService::class)->create([
        'hotel_id' => (int) $hotelId, 'check_in' => '2026-10-05', 'check_out' => '2026-10-07',
        'guest_name' => 'Concurrent guest', 'guest_email' => 'race@example.test',
        'items' => [['room_id' => (int) $roomId, 'board_type_id' => (int) $boardId, 'quantity' => 1, 'adults' => 2]],
    ]);
    echo "RESULT:created\n";
} catch (App\Exceptions\BookingException $e) {
    echo "RESULT:rejected\n";
}
