<?php

namespace App\Http\Controllers\Booking;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookingManagementController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Booking::class);

        $bookings = Booking::visibleTo($request->user())
            ->with('hotel')->latest()->paginate(15);

        return view('booking.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        Gate::authorize('view', $booking);
        $booking->load(['hotel', 'items']);

        return view('booking.manage', [
            'booking' => $booking,
            'guestManagement' => false,
            'cancelUrl' => route('bookings.cancel', $booking),
        ]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        Gate::authorize('cancel', $booking);
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:2000']]);

        try {
            $this->bookingService->cancel($booking, $request->user(), $data['reason'] ?? null);
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking cancelled.');
    }

    public function confirm(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->confirm($booking, $request->user());
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking confirmed.');
    }

    public function complete(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->complete($booking, $request->user());
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking completed.');
    }
}
