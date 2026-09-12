<?php
namespace App\Events;
use App\Enums\BookingNoticeType;
use App\Models\Booking;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class BookingActivity implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    public function __construct(public array $data) {}
    public static function record(Booking $booking, BookingNoticeType $type, ?string $reason = null): void {
        // Immutable event snapshot; queued mail must not rewrite an earlier event with a later status.
        $payment = $booking->payment()->first();
        self::dispatch([
            'type' => $type->value, 'booking_number' => $booking->booking_number,
            'user_id' => $booking->user_id, 'guest_email' => $booking->guest_email,
            'hotel' => $booking->hotel()->value('name'), 'status' => $booking->status->value,
            'payment_status' => $payment?->status->value ?? 'not started', 'reason' => $reason,
            'check_out' => $booking->check_out->toDateString(), 'occurred_at' => now()->toIso8601String(),
        ]);
    }
}
