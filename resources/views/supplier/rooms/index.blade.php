<x-layouts.dashboard>

@include('supplier.hotels.setup._steps')

<div class="flex justify-between items-center mb-6">

    <h1 class="text-2xl font-bold">
        Rooms — {{ $hotel->name }}
    </h1>

    <a
        href="{{ route('supplier.hotels.rooms.create', $hotel) }}"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl"
    >
        + Add Room
    </a>

</div>

<div class="space-y-4">

@forelse($rooms as $room)

<div class="flex gap-6 p-5 bg-gray-900 rounded-2xl shadow">

    <!-- Image -->
    <div class="w-44 h-28 bg-gray-700 rounded-xl overflow-hidden">
        <img
            src="/storage/{{ $room->featuredImage->path ?? '' }}"
            class="w-full h-full object-cover"
        >
    </div>

    <!-- Info -->
    <div class="flex-1 flex flex-col justify-between">

        <div>
            <h2 class="text-lg font-semibold">
                {{ $room->name }}
            </h2>

            <div class="text-gray-400 text-sm mt-1">
                Capacity: {{ $room->capacity }} ·
                Units: {{ $room->total_units }}
            </div>

            <div class="text-gray-400 text-sm">
                €{{ $room->price_per_night }} / night
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-3">

            <a href="{{ route('supplier.hotels.rooms.edit', [$hotel,$room]) }}"
               class="px-3 py-1 bg-blue-600 rounded-lg text-sm">
               Edit
            </a>

            <a href="{{ route('supplier.rooms.images.index', $room) }}"
               class="px-3 py-1 bg-gray-700 rounded-lg text-sm">
               Images
            </a>

            <a href="{{ route('supplier.rooms.inventory.index', $hotel) }}"
               class="px-3 py-1 bg-gray-700 rounded-lg text-sm">
               Inventory
            </a>

        </div>

    </div>

</div>

@empty

<div class="text-gray-400">
    No rooms yet. Add your first room 👇
</div>

@endforelse

</div>

</x-layouts.dashboard>