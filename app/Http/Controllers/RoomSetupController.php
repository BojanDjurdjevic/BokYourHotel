<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Models\Facility;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomSetupController extends Controller
{
    public function images(Room $room)
    {
        Gate::authorize('update', $room->hotel);
        $hotel = $room->hotel;

        return view('supplier.rooms.setup.images', compact('hotel', 'room'));
    }

    public function storeImages(Request $request, Room $room)
    {
        Gate::authorize('update', $room->hotel);
        $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        foreach ($request->file('images', []) as $file) {

            $path = $file->store("rooms/{$room->id}", 'public');

            $room->images()->create([
                'path' => $path,
                'is_featured' => ! $room->images()->where('is_featured', true)->exists(),
            ]);
        }

        return back()->with('success','Images uploaded');
    }

    public function facilities(Room $room)
    {
        Gate::authorize('update', $room->hotel);
        $hotel = $room->hotel;
        $facilities = Facility::all();

        //dd($facilities);

        return view('supplier.rooms.setup.facilities', compact('hotel', 'room', 'facilities'));
    }

    public function facilitiesUpdate(Request $request, Room $room)
    {
        Gate::authorize('update', $room->hotel);
        $request->validate(['facilities' => ['nullable', 'array'], 'facilities.*' => ['integer', 'exists:facilities,id']]);
         $room->facilities()->sync(
            $request->facilities ?? []
        );

        return redirect()
        ->route('supplier.rooms.facilities', $room)
        ->with('success', 'Facilities updated');
    }

    public function inventory(Room $room, Request $request)
    {
        Gate::authorize('update', $room->hotel);
        $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
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
            ->keyBy(fn($i) => $i->date->format('Y-m-d'))
        ;

        if ($request->expectsJson()) {

            return response()->json([

                'label' => $month->translatedFormat('F Y'),

                'dates' => $dates->map(fn ($date) => $date->format('Y-m-d')),

                'inventory' => $inventory->mapWithKeys(fn ($item) => [

                    $item->date->format('Y-m-d') => [

                        'available' => $item->available,

                        'price' => $item->price,

                    ]

                ]),

                'defaults' => [

                    'available' => $room->total_units,

                    'price' => $room->price_per_night ?? 0,

                ]

            ]);

        }

        return view('supplier.rooms.setup.inventory', compact(
            'hotel',
            'room',
            'dates',
            'inventory'
        ));
    }

    public function inventoryUpdate(Request $request, Room $room)
    {
        Gate::authorize('update', $room->hotel);
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

    public function bulkUpdate(Request $request, Room $room)
    {
        Gate::authorize('update', $room->hotel);
        $mydata = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'available' => 'required|integer|min:0',
            'price' => 'required|numeric|gt:0'
        ]);

        $start = Carbon::parse($request->from);
        $end = Carbon::parse($request->to);

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {

            RoomInventory::updateOrCreate(
                [
                    'room_id' => $room->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'available' => $request->available,
                    'price' => $request->price,
                ]
            );
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
