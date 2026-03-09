<x-layouts.dashboard>

<div class="border p-4 rounded">

    <h3 class="font-bold">
        {{ $hotel->name }}
    </h3>

    <p class="text-sm text-gray-500">
        Progress: {{ $hotel->setupProgress() }}%
    </p>

    <a
        href="{{ route('supplier.hotels.setup.info',$hotel) }}"
        class="text-blue-600"
    >
        Continue Setup
    </a>

</div>

<div class="flex justify-between items-center mb-6">

    <h1 class="text-2xl font-bold">
        My Hotels
    </h1>

    <a
        href="{{ route('supplier.hotels.create') }}"
        class="px-4 py-2 bg-emerald-600 rounded-lg hover:bg-emerald-700"
    >
        + Add Hotel
    </a>

</div>

<div class="space-y-4">

    @foreach($hotels as $hotel)

    <a
        href="{{ route('supplier.hotels.edit',$hotel) }}"
        class="flex gap-6 p-4 bg-gray-900 rounded-xl hover:bg-gray-800 transition"
    >

    <div class="w-40 h-28 bg-gray-700 rounded-lg"></div>

    <div class="flex flex-col justify-between">

    <h2 class="text-xl font-semibold">
        {{ $hotel->name }}
    </h2>

    <p class="text-gray-400">
        {{ $hotel->city }}, {{ $hotel->country }}
    </p>

    <span class="text-sm text-gray-500">
        Rooms: {{ $hotel->rooms()->count() }}
    </span>

</div>

</a>

@endforeach

</div>

</x-layouts.dashboard>