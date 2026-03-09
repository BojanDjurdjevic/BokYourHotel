<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Rooms — {{ $hotel->name }}
    </h1>

    <a
        href="{{ route('supplier.hotels.rooms.create', $hotel) }}"
        class="bg-green-600 text-white px-4 py-2 rounded"
    >
        Add Room
    </a>

    <div class="mt-6 space-y-3">

        @foreach($rooms as $room)

        <div class="border p-4 rounded flex justify-between">

        <div>
        <strong>{{ $room->name }}</strong>

        <div class="text-sm text-gray-500">
        Capacity: {{ $room->capacity }}
        </div>
        </div>

        <a
        href="{{ route('supplier.hotels.rooms.edit', [$hotel,$room]) }}"
        class="text-blue-600"
        >
        Edit
        </a>

        </div>

        @endforeach

    </div>

</x-layouts.dashboard>