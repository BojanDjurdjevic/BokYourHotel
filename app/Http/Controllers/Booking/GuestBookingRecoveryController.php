<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuestBookingRecoveryRequest;
use App\Models\Booking;
use App\Notifications\BookingNotice;
use Illuminate\Support\Facades\Notification;

class GuestBookingRecoveryController extends Controller
{
    public function create()
    {
        return view('booking.find');
    }

    public function store(GuestBookingRecoveryRequest $request)
    {
        $data = $request->validated();
        $booking = Booking::query()
            ->where('booking_number', $data['booking_number'])
            ->whereNull('user_id')
            ->whereRaw('LOWER(guest_email) = ?', [strtolower($data['email'])])
            ->with(['hotel', 'payment'])
            ->first();

        if ($booking) {
            try {
                Notification::route('mail', $booking->guest_email)->notify(new BookingNotice([
                    'type' => 'Guest booking access link requested',
                    'booking_number' => $booking->booking_number,
                    'user_id' => null,
                    'guest_email' => $booking->guest_email,
                    'hotel' => $booking->hotel?->name,
                    'status' => $booking->status->value,
                    'payment_status' => $booking->payment?->status->value ?? 'not started',
                    'reason' => null,
                    'check_out' => $booking->check_out->toDateString(),
                    'occurred_at' => now()->toIso8601String(),
                ]));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('status', 'If the booking details match our records, we have sent a secure access link to the booking email address.');
    }
}
