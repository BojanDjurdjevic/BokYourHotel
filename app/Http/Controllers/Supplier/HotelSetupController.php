<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\RoomInventory;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class HotelSetupController extends Controller
{
    public function info(Hotel $hotel)
    {
        return view('supplier.hotels.setup.info', compact('hotel'));
    }

    public function rooms(Hotel $hotel)
    {
        $rooms = $hotel->rooms;

        return view('supplier.hotels.setup.rooms', compact('hotel','rooms'));
    }

    public function inventory(Hotel $hotel)
    {
        return view('supplier.hotels.setup.inventory', compact('hotel'));
    }

    public function storeInventory(Request $request, Hotel $hotel)
    {

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from'
        ]);

        $period = CarbonPeriod::create(
            $request->from,
            $request->to
        );

        foreach ($hotel->rooms as $room) {

            foreach ($period as $date) {

                RoomInventory::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'date' => $date->format('Y-m-d')
                    ],
                    [
                        'available' => $room->total_units,
                        'price' => $room->price_per_night
                    ]
                );

            }

        }

        return redirect()
            ->route('supplier.hotels.setup.images',$hotel)
            ->with('success','Inventory generated successfully');

    }

    public function images(Hotel $hotel)
    {
        $images = $hotel->images;

        return view('supplier.hotels.setup.images', compact('hotel','images'));
    }

    public function publish(Hotel $hotel)
    {
        return view('supplier.hotels.setup.publish', compact('hotel'));
    }
}
