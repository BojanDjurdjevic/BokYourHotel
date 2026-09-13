<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SupplierLifecycleMySqlTest extends SupplierLifecycleTest
{
    private ?string $lifecycleDatabase = null;

    // Run the same HTTP/history contract on real InnoDB, never on the development database.
    public function refreshDatabase(): void
    {
        if (getenv('RUN_LIFECYCLE_MYSQL_TESTS') !== '1') {
            $this->markTestSkipped('Opt in with RUN_LIFECYCLE_MYSQL_TESTS=1; uses disposable MySQL databases.');
        }
        $mysql = config('database.connections.mysql');
        $mysql['database'] = null;
        $mysql['url'] = null;
        config(['database.connections.lifecycle_admin' => $mysql]);
        $this->lifecycleDatabase = 'supplier_lifecycle_test_'.bin2hex(random_bytes(8));
        DB::connection('lifecycle_admin')->statement("CREATE DATABASE `{$this->lifecycleDatabase}`");
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => $this->lifecycleDatabase,
            'database.connections.mysql.url' => null,
        ]);
        DB::purge('mysql');
        Artisan::call('migrate', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->lifecycleDatabase !== null) {
                DB::disconnect('mysql');
                DB::connection('lifecycle_admin')->statement("DROP DATABASE `{$this->lifecycleDatabase}`");
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_migration_down_and_up_preserve_existing_rows_and_restore_constraints(): void
    {
        $booking = $this->reservation();
        $before = $booking->items()->first()->getAttributes();
        $migration = require database_path('migrations/2026_09_13_000001_preserve_supplier_business_history.php');
        $migration->down();
        $this->assertSame('CASCADE', $this->deleteRule('booking_items', 'booking_items_room_id_foreign'));
        $migration->up();
        $this->assertSame('RESTRICT', $this->deleteRule('booking_items', 'booking_items_room_id_foreign'));
        $this->assertSame($before, $booking->items()->first()->getAttributes());
        $this->assertDatabaseCount('bookings', 1);
    }

    private function deleteRule(string $table, string $constraint): string
    {
        return DB::table('information_schema.REFERENTIAL_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $this->lifecycleDatabase)->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)->value('DELETE_RULE');
    }

    public function test_booking_waiting_on_archive_lock_is_rejected_after_archive_commits(): void
    {
        $process = new \Symfony\Component\Process\Process([
            PHP_BINARY, base_path('tests/Support/supplier-lifecycle-worker.php'),
            $this->lifecycleDatabase, (string) $this->hotel->id, (string) $this->room->id, (string) $this->board,
        ], base_path(), ['APP_ENV' => 'testing', 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array']);
        $process->setTimeout(20);
        DB::beginTransaction();
        \App\Models\Hotel::whereKey($this->hotel->id)->lockForUpdate()->firstOrFail();
        try {
            $process->start();
            while (! str_contains($process->getOutput(), 'READY:') && $process->isRunning()) {
                $process->checkTimeout();
                usleep(10000);
            }
            $this->assertMatchesRegularExpression('/READY:(\d+)/', $process->getOutput(), $process->getErrorOutput());
            preg_match('/READY:(\d+)/', $process->getOutput(), $match);
            $deadline = microtime(true) + 8;
            do {
                $waiting = DB::connection('lifecycle_admin')->table('information_schema.innodb_trx')
                    ->where('trx_mysql_thread_id', (int) $match[1])->where('trx_state', 'LOCK WAIT')->exists();
                if ($waiting) break;
                usleep(100000);
            } while (microtime(true) < $deadline);
            $this->assertTrue($waiting, 'Booking creation must wait for the archive hotel lock.');
            app(\App\Services\SupplierLifecycleService::class)->archiveHotel($this->hotel, $this->supplier);
            DB::commit();
            $this->assertSame(0, $process->wait(), $process->getErrorOutput());
            $this->assertStringContainsString('RESULT:rejected', $process->getOutput());
            $this->assertDatabaseCount('bookings', 0);
        } finally {
            if (DB::transactionLevel() > 0) DB::rollBack();
            if ($process->isRunning()) $process->stop();
        }
    }
}
