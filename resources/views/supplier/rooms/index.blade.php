<x-layouts.dashboard>
    <div class="max-w-5xl mx-auto">

    <div class="flex justify-between mb-6">

    <h1 class="text-2xl font-bold">
    Rooms — {{ $hotel->name }}
    </h1>

    <a
    href="{{ route('supplier.hotels.rooms.create', $hotel) }}"
    class="bg-green-600 text-white px-4 py-2 rounded"
    >
    Add Room
    </a>

    </div>

    <table class="w-full border">

    <thead class="bg-gray-800">
    <tr>
    <th class="p-2 text-left">Room</th>
    <th>Capacity</th>
    <th>Price</th>
    <th>Total Units</th>
    <th></th>
    </tr>
    </thead>

    <tbody>

    @foreach($hotel->rooms as $room)

    <tr class="border-t">

    <td class="p-2">{{ $room->name }}</td>
    <td class="text-center">{{ $room->capacity }}</td>
    <td class="text-center">€{{ $room->price_per_night }}</td>
    <td class="text-center">{{ $room->total_units }}</td>

    <td class="text-right pr-3">

    <a
    href="{{ route('supplier.hotels.rooms.edit', [$hotel, $room]) }}"
    class="text-blue-600"
    >
    Edit
    </a>

    </td>

    </tr>

    @endforeach

    </tbody>

    </table>

    </div>
</x-layouts.dashboard>