<x-layouts.dashboard>

    <div class="max-w-3xl mx-auto">

        @include('supplier.rooms.setup._steps', [
            'hotel' => $hotel,
            'room' => $room,
            'step' => 'info'
        ])

        <h1 class="text-2xl font-bold mb-6">
            {{ $room->exists ? 'Edit Room' : 'Create Room' }}
        </h1>

        <form method="POST"
            action="{{ $room->exists
                ? route('supplier.hotels.rooms.update', [$hotel,$room])
                : route('supplier.hotels.rooms.store', $hotel) }}"
        >
            @csrf
            @if($room->exists) @method('PUT') @endif

            <!-- NAME -->
                <div class="mb-4">
                    <label>Name</label>
                    <input type="text" name="name"
                        value="{{ old('name', $room->name) }}"
                        class="w-full p-2 rounded bg-gray-800"
                    >
                </div>

                <!-- ROOM TYPE -->
                <div class="mb-4">
                    <label>Room Type</label>
                    <select name="room_type_id" class="w-full p-2 bg-gray-800 rounded">
                        @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}"
                        @selected($room->room_type_id == $type->id)>
                        {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- BED TYPE -->
                <div class="mb-4">
                    <label>Bed Type</label>
                    <select name="bed_type_id" class="w-full p-2 bg-gray-800 rounded">
                        @foreach($bedTypes as $bed)
                        <option value="{{ $bed->id }}"
                        @selected($room->bed_type_id == $bed->id)>
                            {{ $bed->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- CAPACITY -->
                <div class="mb-4">
                    <label>Capacity</label>
                    <input type="number" name="capacity"
                        value="{{ old('capacity', $room->capacity) }}"
                        class="w-full p-2 bg-gray-800 rounded"
                    >
                </div>

                <!-- FACILITIES -->

                <div class="flex gap-2 flex-wrap mt-2">

                    @foreach($room->facilities as $facility)

                    <span class="text-xs bg-gray-800 px-2 py-1 rounded flex items-center gap-1">

                    <span>
                        {{ config('facility_icons')[$facility->icon] ?? '❔' }}
                    </span>

                        {{ $facility->name }}

                    </span>

                    @endforeach

                </div>

                <!-- PRICE -->
                <div class="mb-4">
                    <label>Base price</label>
                    <input type="number" step="0.01"
                        name="price_per_night"
                        value="{{ old('price_per_night', $room->price_per_night) }}"
                        class="w-full p-2 bg-gray-800 rounded"
                    >
                </div>

                <!-- UNITS -->
                <div class="mb-6">
                    <label>Total units</label>
                    <input type="number"
                        name="total_units"
                        value="{{ old('total_units', $room->total_units) }}"
                        class="w-full p-2 bg-gray-800 rounded"
                    >
                </div>

                <div class="flex justify-between">

                    <a href="{{ route('supplier.hotels.rooms.index', $hotel) }}"
                        class="px-4 py-2 bg-gray-700 rounded-lg"
                    >
                        Cancel
                    </a>

                    <button class="px-4 py-2 bg-green-600 rounded-lg">
                        Save
                    </button>

                </div>

        </form>

    </div>

</x-layouts.dashboard>