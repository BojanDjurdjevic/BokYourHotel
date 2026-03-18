<x-layouts.dashboard>

    @include('supplier.rooms.setup._steps', [
        'hotel' => $hotel,
        'room' => $room ?? null,
        'step' => 'info'
    ])

    <div class="max-w-5xl mx-auto">

        <div class="bg-gray-900 p-6 rounded-2xl">

            <h2 class="text-xl font-semibold mb-6">
                Room Info
            </h2>

            <form method="POST"
                action="{{ isset($room)
                ? route('supplier.hotels.rooms.update', [$hotel,$room])
                : route('supplier.hotels.rooms.store', $hotel) }}"
            >

                @csrf
                @if(isset($room)) @method('PUT') @endif

                <div class="grid grid-cols-2 gap-4">

                <div>
                <label>Name</label>
                <input type="text" name="name"
                    value="{{ old('name',$room->name ?? '') }}"
                    class="w-full p-2 rounded bg-gray-800"
                >
                </div>

                <div>
                <label>Capacity</label>
                <input type="number" name="capacity"
                    value="{{ old('capacity',$room->capacity ?? '') }}"
                    class="w-full p-2 rounded bg-gray-800"
                >
                </div>

                <div>
                <label>Price per night</label>
                <input type="number" step="0.01" name="price_per_night"
                    value="{{ old('price_per_night',$room->price_per_night ?? '') }}"
                    class="w-full p-2 rounded bg-gray-800"
                >
                </div>

                <div>
                <label>Total units</label>
                <input type="number" name="total_units"
                    value="{{ old('total_units',$room->total_units ?? '') }}"
                    class="w-full p-2 rounded bg-gray-800"
                >
                </div>

                </div>

                <div class="mt-6 flex justify-between">

                <a href="{{ route('supplier.hotels.rooms.index',$hotel) }}"
                    
                >
                    Cancel
                </a>

                <button class="px-6 py-2 bg-blue-600 rounded">
                    Save & Continue
                </button>

                </div>

            </form>

        </div>
    </div>
</x-layouts.dashboard>