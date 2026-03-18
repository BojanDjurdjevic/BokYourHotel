<x-layouts.dashboard>

    @include('supplier.rooms.setup._steps', [
        'hotel' => $hotel,
        'room' => $room,
        'step' => 'images'
    ])

    <div class="max-w-5xl mx-auto">

        <div class="bg-gray-900 p-6 rounded-2xl">

            <h2 class="text-xl font-semibold mb-6">
                Room Images
            </h2>

            <form method="POST"
                action="{{ route('supplier.rooms.images.store',$room) }}"
                enctype="multipart/form-data"
            >

                @csrf

                <input type="file" name="images[]" multiple
                class="mb-4">

                <button class="bg-blue-600 px-4 py-2 rounded">
                    Upload
                </button>

            </form>

            <div class="grid grid-cols-4 gap-4 mt-6">

                @foreach($room->images as $img)

                    <div class="relative">

                        <img src="/storage/{{ $img->path }}"
                            class="rounded-lg"
                        >

                        @if($img->is_featured)
                        <span class="absolute top-1 left-1 bg-green-600 text-xs px-2 rounded">
                            Featured
                        </span>
                        @endif

                    </div>

                @endforeach

            </div>

        </div>
    </div>
</x-layouts.dashboard>