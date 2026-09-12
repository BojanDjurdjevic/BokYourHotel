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
    'payments.fake_enabled' => true,
    'database.default' => 'mysql',
    'database.connections.mysql.database' => $database,
    'database.connections.mysql.url' => null,
]);
Illuminate\Support\Facades\DB::purge('mysql');
Carbon\Carbon::setTestNow('2026-09-15 14:00:00');

$booking = App\Models\Booking::findOrFail($bookingId);
$actor = App\Models\User::findOrFail($actorId);
$item = $booking->items()->firstOrFail();
$room = $item->room;
$snapshot = app(App\Services\InventoryService::class)->snapshot($room, $actor, $booking->check_in->toDateString(), $booking->check_out->copy()->subDay()->toDateString());
if ($action === 'expire') Carbon\Carbon::setTestNow('2026-09-15 14:30:00');
if ($action === 'pay_before_expiry') Carbon\Carbon::setTestNow('2026-09-15 14:29:59');
$thread = Illuminate\Support\Facades\DB::selectOne('SELECT CONNECTION_ID() AS id')->id;
echo "READY:$thread\n";
flush();

try {
    $service = app(App\Services\BookingService::class);
    $result = match ($action) {
        'cancel' => $service->cancel($booking, $actor),
        'confirm' => $service->confirm($booking, $actor),
        'pay', 'pay_before_expiry' => app(App\Services\FakePaymentService::class)->submit($booking, null, 1, 'success'),
        'expire' => $service->expire($booking),
        'inventory' => app(App\Services\InventoryService::class)->update($room, $actor, $snapshot),
        'create' => $service->create([
            'hotel_id' => $booking->hotel_id, 'check_in' => $booking->check_in->toDateString(), 'check_out' => $booking->check_out->toDateString(),
            'guest_name' => 'Concurrent Guest', 'guest_email' => 'race@example.test',
            'items' => [['room_id' => $room->id, 'board_type_id' => $item->board_type_id, 'quantity' => 2, 'adults' => 2]],
        ]),
        default => throw new RuntimeException('Unknown test action.'),
    };
    echo $result === false ? "RESULT:rejected\n" : "RESULT:success\n";
} catch (App\Exceptions\BookingException | Symfony\Component\HttpKernel\Exception\HttpException $e) {
    if ($e instanceof Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() !== 409) throw $e;
    echo "RESULT:rejected\n";
}
