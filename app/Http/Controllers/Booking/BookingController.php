<?php

namespace App\Http\Controllers\Booking;

use App\Exceptions\BookingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookingRequest;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    public function show(Hotel $hotel)
    {
        $hotel->load([
            'rooms.featuredImage',
            'rooms.boardTypes',
            'rooms.roomType',
        ]);

        return view('booking.show', compact('hotel'));
    }

    public function store(CreateBookingRequest $request)
    {
        try {

            $booking = $this->bookingService->create(
                $request->validated()
            );

            return redirect()
                ->route('booking.success', $booking)
                ->with(
                    'success',
                    'Booking successfully created.'
                );

        } catch (BookingException $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'booking' => $e->getMessage()
                ]);

        } catch (\Throwable $e) {

            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'booking' =>'Unexpected error occurred.'
                ]);
        }
    }

    public function success(Booking $booking)
    {
        return view(
            'booking.success',
            compact('booking')
        );
    }

    public function availability(Hotel $hotel, Request $request)
    {
        
    }
}
