<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use Carbon\Carbon;

class RoomInventoryController extends Controller
{

    public function index(Hotel $hotel)
    {
        Gate::authorize('update', $hotel);

        $rooms = $hotel->rooms()->select('id','name')->get();

        if ($rooms->isEmpty()) {
            return redirect()->route('supplier.hotels.rooms.index', $hotel)->with('error', 'Add a room before opening inventory.');
        }

        return view(
            'supplier.inventory.calendar',
            compact('hotel','rooms')
        );

    }


    public function monthData(Request $request, Hotel $hotel)
    {
        Gate::authorize('update', $hotel);
        $request->validate(['room_id' => ['required', 'integer'], 'month' => ['required', 'date_format:Y-m']]);
        $room = $hotel->rooms()->findOrFail($request->room_id);

        $roomId = $request->room_id;

        $month = Carbon::parse($request->month);

        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $inventory = RoomInventory::where('room_id',$roomId)
            ->whereBetween('date',[$start,$end])
            ->get()
            ->keyBy(function ($item) {
                return $item->date->toDateString();
        });

        $days = [];

        $cursor = $start->copy();

        while($cursor <= $end){

        $date = $cursor->toDateString();

        $row = $inventory[$date] ?? null;

        $days[] = [

            'date' => $date,
            'version' => (int) ($row?->version ?? 0),
            'available' => $row?->available ?? $room->total_units,
            'price' => $row?->price ?? $room->price_per_night

        ];

        $cursor->addDay();

        }

        return response()->json($days);

    }


    public function updateDay(Request $request, \App\Services\InventoryService $inventory)
    {
        $request->validate(['room_id' => ['required', 'integer', 'exists:rooms,id']]);
        $inventory->update(Room::findOrFail($request->room_id), $request->user(), [$request->only('date', 'version', 'available', 'price')]);
        return response()->json(['success' => true]);
    }
}