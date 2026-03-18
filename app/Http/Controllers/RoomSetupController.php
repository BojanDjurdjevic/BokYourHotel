<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomSetupController extends Controller
{
    public function images(Room $room)
    {
        $hotel = $room->hotel;

        return view('supplier.rooms.setup.images', compact('hotel', 'room'));
    }

    public function storeImages()
    {

    }

    public function facilities(Room $room)
    {
        $hotel = $room->hotel;
        $facilities = Facility::all();

        return view('supplier.rooms.setup.facilities', compact('hotel', 'room', 'facilities'));
    }

    public function facilitiesUpdate()
    {
        
    }

    public function inventory(Room $room)
    {
        $hotel = $room->hotel;

        return view('supplier.rooms.setup.inventory', compact('hotel', 'room'));
    }
}
