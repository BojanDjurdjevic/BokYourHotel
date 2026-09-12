<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Enums\BookingStatus;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotelCount = auth()->user()->hotels()->count();
        $bookings = Booking::whereHas('hotel', fn ($query) => $query->where('supplier_id', auth()->id()));
        $pendingCount = (clone $bookings)->where('status', BookingStatus::Pending)->count();
        $confirmedCount = (clone $bookings)->where('status', BookingStatus::Confirmed)->count();

        return view('supplier.dashboard', compact('hotelCount', 'pendingCount', 'confirmedCount'));
    }

    public function pending()
    {
        return $this->bookings(BookingStatus::Pending);
    }

    public function confirmed()
    {
        return $this->bookings(BookingStatus::Confirmed);
    }

    private function bookings(BookingStatus $status)
    {
        $bookings = Booking::whereHas('hotel', fn ($query) => $query->where('supplier_id', auth()->id()))
            ->where('status', $status)->with(['hotel', 'payment'])->latest()->paginate(15);

        return view('booking.index', ['bookings' => $bookings, 'title' => ucfirst($status->value).' hotel bookings']);
    }

    /**
     * Show the form for creating a new resource.·
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
