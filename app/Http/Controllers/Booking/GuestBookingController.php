<?php

namespace App\Http\Controllers\Booking;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class GuestBookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function show(Booking $booking)
    {
        abort_unless($booking->user_id === null, 403);
        $booking->load(['hotel', 'items', 'payment']);

        return response()->view('booking.manage', [
            'booking' => $booking,
            'guestManagement' => true,
            'cancelUrl' => URL::temporarySignedRoute('guest.bookings.cancel', $booking->check_out->copy()->endOfDay(), $booking),
        ])->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'private, no-store');
    }

    public function cancel(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === null, 403);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->bookingService->cancel($booking, null, $data['reason'] ?? null);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->to(URL::temporarySignedRoute(
            'guest.bookings.show', $booking->check_out->copy()->endOfDay(), $booking
        ))->with('success', 'Booking cancelled.');
    }
}
