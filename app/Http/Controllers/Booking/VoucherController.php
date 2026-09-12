<?php
namespace App\Http\Controllers\Booking;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
class VoucherController extends Controller
{
    public function __invoke(Request $request, Booking $booking) {
        if ($request->routeIs('guest.bookings.voucher')) abort_unless($booking->user_id === null, 403);
        else Gate::authorize('view', $booking);
        $booking->load(['hotel','items','payment']);
        return response()->view('booking.voucher', ['booking' => $booking, 'generatedAt' => now()])
            ->header('Content-Disposition', 'attachment; filename="voucher-'.preg_replace('/[^A-Za-z0-9-]/', '', $booking->booking_number).'.html"')
            ->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
