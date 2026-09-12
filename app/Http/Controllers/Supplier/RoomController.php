<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\BedType;
use App\Models\BoardType;
use App\Http\Requests\RoomRequest;
use App\Models\Facility;

class RoomController extends Controller
{
    public function show(Hotel $hotel, Room $room)
    {
        Gate::authorize('update', $hotel);
        abort_unless($room->hotel_id === $hotel->id, 404);
        return redirect()->route('supplier.hotels.rooms.edit', [$hotel, $room]);
    }

    public function destroy(Hotel $hotel, Room $room)
    {
        Gate::authorize('update', $hotel);
        abort_unless($room->hotel_id === $hotel->id, 404);
        abort(405, 'Room deletion is not available.');
    }

    public function index(Hotel $hotel)
    {
        Gate::authorize('update', $hotel);
        $rooms = $hotel->rooms()->with('featuredImage')->paginate(12);

        return view('supplier.rooms.index', compact('hotel','rooms'));
    }

    public function create(Hotel $hotel)
    {
        Gate::authorize('update', $hotel);
        //dd($hotel->supplier_id, auth()->id());
        $facilities = Facility::all();
        $roomTypes = RoomType::all();
        $bedTypes = BedType::all();
        $boardTypes = BoardType::all();

        return view('supplier.rooms.create', compact(
            'hotel',
            'facilities',
            'roomTypes',
            'bedTypes',
            'boardTypes'
        ));
    }

    public function store(RoomRequest $request, Hotel $hotel)
    {
        Gate::authorize('update', $hotel);
        //dd($request->validated());
        $room = $hotel->rooms()->create(
            $request->validated()
        );
        /*
        $room->boardTypes()->sync(
        $request->board_types ?? []
        ); */

        $syncData = [];

        foreach ($request->board_types ?? [] as $boardTypeId => $data) {

            if (empty($data['enabled'])) {
                continue;
            }

            $syncData[$boardTypeId] = [
                'price' => $data['price'] ?? 0
            ];
        }

        $room->boardTypes()->sync($syncData);

        $room->facilities()->sync($request->facilities ??  []);

        if(!$hotel->published)
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
        Gate::authorize('update', $hotel);
        abort_unless($room->hotel_id === $hotel->id, 404);
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
        Gate::authorize('update', $hotel);
        abort_unless($room->hotel_id === $hotel->id, 404);
        //dd($request->validated());
        $room->update(
            $request->validated()
        );

        $syncData = [];
        foreach ($request->validated('board_types', []) as $boardTypeId => $data) {
            if (! empty($data['enabled'])) {
                $syncData[$boardTypeId] = ['price' => $data['price'] ?? 0];
            }
        }
        $room->boardTypes()->sync($syncData);

        // Facilities are managed on the separate facilities step.

        return redirect()
            ->route('supplier.hotels.rooms.index', $room->hotel)
            ->with('success','Room updated');
    }

}
