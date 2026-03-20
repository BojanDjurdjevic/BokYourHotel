<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use Illuminate\Http\Request;

class RoomSetupController extends Controller
{
    public function images(Room $room)
    {
        $hotel = $room->hotel;

        return view('supplier.rooms.setup.images', compact('hotel', 'room'));
    }

    public function storeImages(Request $request, Room $room)
    {
        foreach ($request->file('images', []) as $file) {

            $path = $file->store('rooms', 'public');

            $room->images()->create([
                'path' => $path
            ]);
        }

        return back()->with('success','Images uploaded');
    }

    public function facilities(Room $room)
    {
        $hotel = $room->hotel;
        $facilities = Facility::all();

        return view('supplier.rooms.setup.facilities', compact('hotel', 'room', 'facilities'));
    }

    public function facilitiesUpdate(Request $request, Room $room)
    {
         $room->facilities()->sync(
            $request->facilities ?? []
        );

        return redirect()
        ->route('supplier.rooms.facilities', $room)
        ->with('success', 'Facilities updated');
    }

    public function inventory(Room $room, Request $request)
    {
        $hotel = $room->hotel;

        $month = $request->month
            ? \Carbon\Carbon::parse($request->month)
            : now();

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $dates = collect();
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $dates->push($date->copy());
        }

        $inventory = $room->inventories()
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn($i) => $i->date->format('Y-m-d'));

        return view('supplier.rooms.setup.inventory', compact(
            'hotel',
            'room',
            'dates',
            'inventory'
        ));
    }

    public function inventoryUpdate(Request $request, Room $room)
    {
        $request->validate([
            'date' => 'required|date',
            'available' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0'
        ]);

        RoomInventory::updateOrCreate(
            [
                'room_id' => $room->id,
                'date' => $request->date
            ],
            [
                'available' => $request->available,
                'price' => $request->price
            ]
        );


        return response()->json(['success' => true]);
    }
}
