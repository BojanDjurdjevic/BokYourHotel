<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';
    protected $description = 'Release expired, pending unpaid reservation holds';

    public function handle(BookingService $service): int
    {
        $expired = 0;
        $failed = 0;
        Booking::where('status', BookingStatus::Pending)->where('locked_until', '<=', now())
            ->select('id')->chunkById(200, function ($bookings) use ($service, &$expired, &$failed) {
                foreach ($bookings as $booking) {
                    try { $expired += (int) $service->expire($booking); }
                    catch (\Throwable $e) { report($e); $failed++; }
                }
            });
        $this->info("Expired: $expired; failed: $failed.");
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
