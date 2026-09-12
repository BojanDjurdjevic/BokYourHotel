<?php

namespace App\Http\Controllers\Booking;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\FakePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    public function __construct(private FakePaymentService $payments) {}

    private function isGuest(Request $request, Booking $booking): bool
    {
        $guest = $request->routeIs('guest.payments.*');
        if ($guest) {
            abort_unless($booking->user_id === null, 403);
        } else {
            Gate::authorize('pay', $booking);
        }

        return $guest;
    }

    public function show(Request $request, Booking $booking)
    {
        $guest = $this->isGuest($request, $booking);
        $booking->load('payment');
        $attempt = $booking->payment?->attempt ?? 1;
        $parameters = ['booking' => $booking, 'attempt' => $attempt];
        $expires = $booking->check_out->copy()->endOfDay();

        return response()->view('booking.checkout', [
            'booking' => $booking,
            'attempt' => $attempt,
            'submitUrl' => $guest ? URL::temporarySignedRoute('guest.payments.submit', $expires, $parameters) : route('payments.submit', $booking),
            'retryUrl' => $guest ? URL::temporarySignedRoute('guest.payments.retry', $expires, $parameters) : route('payments.retry', $booking),
            'manageUrl' => $guest ? URL::temporarySignedRoute('guest.bookings.show', $expires, $booking) : route('bookings.show', $booking),
            'successUrl' => URL::temporarySignedRoute('booking.success', $expires, $booking),
        ])->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'private, no-store');
    }

    public function submit(Request $request, Booking $booking)
    {
        return $this->process($request, $booking, false);
    }

    public function retry(Request $request, Booking $booking)
    {
        return $this->process($request, $booking, true);
    }

    private function process(Request $request, Booking $booking, bool $retry)
    {
        $guest = $this->isGuest($request, $booking);
        $data = $request->validate([
            'attempt' => ['required', 'integer', 'min:1', 'max:4294967294'],
            'outcome' => $retry ? ['prohibited'] : ['required', 'in:success,failure'],
        ]);
        // The signed query controls the guest attempt, never a replacement POST field.
        $attempt = $guest ? (int) $request->query('attempt') : (int) $data['attempt'];

        try {
            if ($retry) {
                $this->payments->retry($booking, $guest ? null : $request->user(), $attempt);
            } else {
                $this->payments->submit($booking, $guest ? null : $request->user(), $attempt, $data['outcome']);
            }
        } catch (BookingException $e) {
            return back()->with('error', $e->getMessage());
        }

        $url = $guest
            ? URL::temporarySignedRoute('guest.payments.show', $booking->check_out->copy()->endOfDay(), $booking)
            : route('payments.show', $booking);

        return redirect()->to($url);
    }
}
