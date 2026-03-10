<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\BedType;
use App\Models\BoardType;
use App\Http\Requests\RoomRequest;

class RoomController extends Controller
{

    public function index(Hotel $hotel)
    {
        $rooms = $hotel->rooms;

        return view('supplier.rooms.index', compact('hotel','rooms'));
    }

    public function create(Hotel $hotel)
    {
        //dd($hotel->supplier_id, auth()->id());
        $roomTypes = RoomType::all();
        $bedTypes = BedType::all();
        $boardTypes = BoardType::all();

        return view('supplier.rooms.create', compact(
            'hotel',
            'roomTypes',
            'bedTypes',
            'boardTypes'
        ));
    }

    public function store(RoomRequest $request, Hotel $hotel)
    {
        //dd($request->all());
        $room = $hotel->rooms()->create(
            $request->validated()
        );

        $room->boardTypes()->sync(
        $request->board_types ?? []
        );
        if($hotel->published)
        return redirect()
            ->route('supplier.hotels.setup.inventory', $hotel)
            ->with('success','Room created');
        else
        return redirect()
            ->route('supplier.hotels.rooms.index', $hotel)
            ->with('success','Room created');
    }

    public function edit(Hotel $hotel, Room $room)
    {
        $roomTypes = RoomType::all();
        $bedTypes = BedType::all();
        $boardTypes = BoardType::all();

        return view('supplier.rooms.edit', compact(
            'room',
            'hotel',
            'roomTypes',
            'bedTypes',
            'boardTypes'
        ));
    }

    public function update(RoomRequest $request, Hotel $hotel, Room $room)
    {
        //dd($request->validated());
        $room->update(
            $request->validated()
        );

        $room->boardTypes()->sync(
            $request->board_types ?? []
        );

        return redirect()
            ->route('supplier.hotels.rooms.index', $room->hotel)
            ->with('success','Room updated');
    }

}