<x-layouts.dashboard>
    <div class="max-w-5xl mx-auto">

        <h1 class="text-2xl font-bold mb-4">
            {{ $hotel->name }}
        </h1>

        <p class="text-gray-600 mb-6">
            {{ $hotel->description }}
        </p>

        <div class="grid grid-cols-3 gap-6">

            <div class="col-span-2">

                <h2 class="font-semibold mb-2">Facilities</h2>

                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($hotel->facilities ?? [] as $facility)
                        <span class="bg-gray-200 px-2 py-1 rounded text-sm">
                            {{ $facility }}
                        </span>
                    @endforeach
                </div>

                <h2 class="font-semibold mb-2">Rooms</h2>

                <ul class="space-y-2">
                    @foreach($hotel->rooms as $room)
                        <li class="border p-3 rounded flex justify-between">
                            <div>
                                <strong>{{ $room->name }}</strong>
                                <div class="text-sm text-gray-500">
                                    Capacity: {{ $room->capacity }}
                                </div>
                            </div>

                            <div>
                                €{{ $room->price_per_night }}
                            </div>
                        </li>
                    @endforeach
                </ul>

            </div>

            <div>

                <h2 class="font-semibold mb-2">Images</h2>

                <div class="grid grid-cols-2 gap-2">
                    @foreach($hotel->images as $image)
                        <img src="{{ asset('storage/'.$image->path) }}" class="rounded">
                    @endforeach
                </div>

            </div>

        </div>

    </div>
</x-layouts.dashboard>