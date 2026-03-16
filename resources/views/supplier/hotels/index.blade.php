<x-layouts.dashboard>

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
        href="{{ route('supplier.hotels.setup.info',$hotel) }}"
        class="flex gap-6 p-4 bg-gray-900 rounded-xl hover:bg-gray-800 transition"
    >

    <div class="w-40 h-28 bg-gray-700 rounded-lg overflow-hidden">
        @if ($hotel->featuredImage)
            <img src="/storage/{{ $hotel->featuredImage->path }}" alt="Hotel image"
                class="w-full h-full object-cover object-center"
            />
        @endif
        
    </div>

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

    @if ($hotel->published)
        <span class="text-xs bg-green-600 px-2 py-1 rounded">
            Published
        </span> 
    @else

        <span class="text-xs bg-amber-600 px-2 py-1 rounded">
            Draft
        </span> 

    @endif
    

</div>

</a>

@endforeach

<h2 class="text-lg font-semibold mb-4">
Setup Required
</h2>

@foreach($incompleteHotels as $hotel)

<div class="border p-4 rounded mb-4">

<strong>{{ $hotel->name }}</strong>

<p>
Progress: {{ $hotel->setupProgress() }}%
</p>

<a
href="{{ route('supplier.hotels.setup.info',$hotel) }}"
>
Continue Setup
</a>

</div>

@endforeach

</div>

</x-layouts.dashboard>