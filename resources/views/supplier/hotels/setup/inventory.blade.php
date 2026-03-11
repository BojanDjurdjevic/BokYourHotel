<x-layouts.dashboard>

    @include('supplier.hotels.setup._steps')

    <h1 class="text-xl font-bold mb-6">
        Inventory Setup
    </h1>

    <p class="text-gray-500 mb-6">
        Select a date range to generate availability for all rooms.
    </p>

    {{-- ROOM LIST --}}

    <div class="mb-8 border rounded p-4 bg-gray-900">
        
        <h2 class="font-semibold mb-3">
            Rooms in this hotel
        </h2>

        <ul class="space-y-2 text-sm text-gray-300">
            @foreach($hotel->rooms as $room)

                <li class="flex justify-between border-b border-gray-700 pb-2">

                    <span>
                        {{ $room->name }}
                    </span>

                    <span class="text-gray-400">
                        {{ $room->total_units }} units ·
                        €{{ $room->price_per_night }}/night
                    </span>

                </li>

            @endforeach
        </ul>

    </div>

    <form method="POST"
        action="{{ route('supplier.hotels.inventory.store',$hotel) }}"
        class="space-y-6 max-w-lg">

    @csrf

        <div>
            <label class="block text-sm font-medium mb-1">
                From date
            </label>

            <input type="date"
                name="from"
                class="border rounded w-full p-2 text-white"
                required>
            </div>

            <div>
            <label class="block text-sm font-medium mb-1">
                To date
            </label>

            <input type="date"
                name="to"·
                class="border rounded w-full p-2"
                required
            >
        </div>

        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Generate Inventory
        </button>

    </form>

</x-layouts.dashboard>