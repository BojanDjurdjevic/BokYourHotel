<x-app-layout>
    <div class="space-y-8">
        <a href="{{ route('hotels.index', request()->query()) }}" class="text-blue-400">Back to hotels</a>
        <div><h1 class="text-3xl font-bold">{{ $hotel->name }}</h1><p class="mt-2 text-gray-400">{{ $hotel->address }}, {{ $hotel->city }}, {{ $hotel->country }}</p></div>
        @if($hotel->images->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($hotel->images as $image)
                    <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $hotel->name }}" class="w-full h-64 object-cover rounded-2xl" loading="lazy">
                @endforeach
            </div>
        @endif
        <p class="text-gray-300 whitespace-pre-line">{{ $hotel->description }}</p>
        <a href="{{ route('booking.show', ['hotel' => $hotel] + request()->only('check_in','check_out','adults','children')) }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl">Check availability and book</a>
        <h2 class="text-2xl font-semibold">Rooms</h2>
        <p class="text-gray-400">Choose dates to see current availability and the final price for your stay.</p>
        <div class="grid md:grid-cols-2 gap-6">
            @forelse($hotel->rooms as $room)
                <article class="p-6 bg-gray-900 border border-gray-800 rounded-2xl space-y-3">
                    @if($room->featuredImage)
                        <img src="{{ asset('storage/'.$room->featuredImage->path) }}" alt="{{ $room->name }}" class="h-48 w-full object-cover rounded-xl" loading="lazy">
                    @endif
                    <h3 class="text-xl font-semibold">{{ $room->name }}</h3>
                    <p class="text-gray-400">Up to {{ $room->capacity }} guests per room</p>
                    <p>{{ $room->description }}</p>
                    <p class="text-sm text-gray-400">{{ $room->facilities->pluck('name')->join(' · ') }}</p>
                    <p class="text-sm">Board options: {{ $room->boardTypes->pluck('name')->join(', ') ?: 'Not available yet' }}</p>
                </article>
            @empty
                <p class="text-gray-400">Rooms are not available yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
