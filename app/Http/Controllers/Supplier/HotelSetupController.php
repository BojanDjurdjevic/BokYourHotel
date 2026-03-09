<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
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
