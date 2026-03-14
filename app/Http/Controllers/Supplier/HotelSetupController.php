<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryRequest;
use App\Models\Hotel;
use App\Models\RoomInventory;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $rooms = $hotel->rooms()->get();

        return view('supplier.hotels.setup.inventory', [
            'hotel' => $hotel,
            'rooms' => $rooms
        ]);
    }

    public function storeInventory(Request $request, Hotel $hotel)
    {
        //dd($request->all());

        $request->validate([

            'room_id' => 'required|exists:rooms,id',

            'inventory_json' => 'required|string'

        ]);

        $inventory = json_decode($request->inventory_json, true);

        if(!$inventory){
            return back()->withErrors([
                'inventory' => 'Generate inventory first.'
            ]);
        }
        $data = [];

        foreach ($inventory as $item) {

            $data[] = [

                'room_id' => $request->room_id,

                'date' => $item['date'],

                'available' => $item['available'],

                'price' => $item['price'],

                'created_at' => now(),
                'updated_at' => now()

            ];

        }

        RoomInventory::upsert(
            $data,
            ['room_id','date'],
            ['available','price','updated_at']
        );

        return redirect()
            ->route('supplier.hotels.setup.images',$hotel)
            ->with('success','Inventory generated');
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

    public function publishHotel(Hotel $hotel) 
    {
        $hotel->published = 1;
        $hotel->save(); 

        return redirect()->back()->with('success', "Hotel $hotel->name is successfully published!");
    }
}
