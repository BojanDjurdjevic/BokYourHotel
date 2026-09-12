<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class BookingManagementConcurrencyTest extends TestCase
{
    private ?string $testDatabase = null;
    private Booking $booking;
    private User $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        if (getenv('RUN_BOOKING_MYSQL_TESTS') !== '1') {
            $this->markTestSkipped('Opt in with RUN_BOOKING_MYSQL_TESTS=1; creates and removes a disposable MySQL database.');
        }

        $mysql = config('database.connections.mysql');
        $mysql['database'] = null;
        $mysql['url'] = null;
        config(['database.connections.booking_test_admin' => $mysql]);

        $database = 'booking_management_test_' . bin2hex(random_bytes(8));
        DB::connection('booking_test_admin')->statement("CREATE DATABASE `$database`");
        $this->testDatabase = $database;
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.url' => null,
        ]);
        DB::purge('mysql');
        Artisan::call('migrate', ['--force' => true]);
        $this->travelTo(Carbon::parse('2026-09-15 14:00:00'));

        $this->supplier = User::factory()->create(['role' => 'supplier']);
        $hotel = Hotel::create([
            'supplier_id' => $this->supplier->id, 'name' => 'Concurrency Hotel',
            'city' => 'Belgrade', 'country' => 'Serbia', 'address' => 'Test Street',
        ]);
        $room = Room::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => DB::table('room_types')->insertGetId(['name' => 'Double']),
            'bed_type_id' => DB::table('bed_types')->insertGetId(['name' => 'Double']),
            'name' => 'Double', 'capacity' => 2, 'total_units' => 3, 'price_per_night' => 100,
        ]);
        $board = DB::table('board_types')->insertGetId(['code' => 'BB', 'name' => 'Breakfast']);
        $room->boardTypes()->attach($board, ['price' => 10]);
        $this->booking = app(BookingService::class)->create([
            'hotel_id' => $hotel->id, 'check_in' => '2026-09-18', 'check_out' => '2026-09-20',
            'guest_name' => 'Guest', 'guest_email' => 'guest@example.com',
            'items' => [['room_id' => $room->id, 'board_type_id' => $board, 'quantity' => 3, 'adults' => 3]],
        ]);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->testDatabase !== null) {
                DB::disconnect('mysql');
                // This exact, randomly generated database was created by this test.
                DB::connection('booking_test_admin')->statement("DROP DATABASE `{$this->testDatabase}`");
            }
        } finally {
            parent::tearDown();
        }
    }

    private function runConcurrentActions(array $actions): array
    {
        $processes = [];
        $threads = [];
        DB::beginTransaction();
        Booking::whereKey($this->booking->id)->lockForUpdate()->firstOrFail();

        try {
            foreach ($actions as $action) {
                $process = new Process([
                    PHP_BINARY, base_path('tests/Support/booking-management-worker.php'),
                    $this->testDatabase, $action, (string) $this->booking->id, (string) $this->supplier->id,
                ], base_path(), ['APP_ENV' => 'testing', 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array']);
                $process->setTimeout(20);
                $processes[] = $process;
                $process->start();
                $process->waitUntil(fn () => str_contains($process->getOutput(), 'READY:'));
                $this->assertMatchesRegularExpression('/READY:(\d+)/', $process->getOutput(), $process->getErrorOutput());
                preg_match('/READY:(\d+)/', $process->getOutput(), $match);
                $threads[] = (int) $match[1];
            }

            // Prove both workers actually reached a database lock wait before releasing them.
            $waiting = 0;
            $deadline = microtime(true) + 8;
            do {
                $waiting = DB::connection('booking_test_admin')->table('information_schema.innodb_trx')
                    ->whereIn('trx_mysql_thread_id', $threads)->where('trx_state', 'LOCK WAIT')->count();
                if ($waiting === 2) break;
                usleep(100000);
            } while (microtime(true) < $deadline);
            $this->assertSame(2, $waiting, 'Both workers must contend on the booking lock.');
            DB::commit();

            $results = [];
            foreach ($processes as $process) {
                $this->assertSame(0, $process->wait(), $process->getErrorOutput());
                preg_match('/RESULT:(\w+)/', $process->getOutput(), $match);
                $results[] = $match[1];
            }
            return $results;
        } finally {
            if (DB::transactionLevel() > 0) DB::rollBack();
            foreach ($processes as $process) {
                if ($process->isRunning()) $process->stop();
            }
        }
    }

    public function test_parallel_cancellations_restore_inventory_exactly_once(): void
    {
        $results = $this->runConcurrentActions(['cancel', 'cancel']);
        $this->assertEqualsCanonicalizing(['success', 'rejected'], $results);
        $this->assertSame(BookingStatus::Cancelled, $this->booking->refresh()->status);
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
        $this->assertDatabaseCount('booking_items', 1);
    }

    public function test_parallel_confirm_cancel_always_ends_cancelled_with_restored_inventory(): void
    {
        $results = $this->runConcurrentActions(['confirm', 'cancel']);
        $this->assertSame('success', $results[1]);
        $this->assertContains($results[0], ['success', 'rejected']);
        $this->assertSame(BookingStatus::Cancelled, $this->booking->refresh()->status);
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
    }
}
