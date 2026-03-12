<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use Carbon\Carbon;

class RoomInventoryController extends Controller
{

    public function index(Hotel $hotel)
    {

        $rooms = $hotel->rooms()->select('id','name')->get();

        return view(
            'supplier.inventory.calendar',
            compact('hotel','rooms')
        );

    }


    public function monthData(Request $request, Hotel $hotel)
    {

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
            'available' => $row?->available ?? 0,
            'price' => $row?->price ?? 0

        ];

        $cursor->addDay();

        }

        return response()->json($days);

    }


    public function updateDay(Request $request)
    {

        $request->validate([

            'room_id'=>'required|exists:rooms,id',
            'date'=>'required|date',
            'available'=>'required|integer|min:0',
            'price'=>'required|numeric|min:0'

        ]);

        RoomInventory::updateOrCreate(

        [
            'room_id'=>$request->room_id,
            'date'=>$request->date
        ],

        [
            'available'=>$request->available,
            'price'=>$request->price
        ]

        );

        return response()->json(['success'=>true]);

    }

}