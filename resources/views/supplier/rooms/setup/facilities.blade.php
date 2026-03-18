<x-layouts.dashboard>

    @include('supplier.rooms.setup._steps', [
        'hotel' => $hotel,
        'room' => $room,
        'step' => 'facilities'
    ])

    <div class="max-w-5xl mx-auto">

        <div class="bg-gray-900 p-6 rounded-2xl">

            <h2 class="text-xl font-semibold mb-6">
                Room Facilities
            </h2>

            <form method="POST"
                action="{{ route('supplier.rooms.facilities.update',$room) }}"
            >

                @csrf
                @method('PUT')

                <x-facilities-selector
                    :facilities="$facilities"
                    :selected="$room->facilities->pluck('id')->toArray()"
                />

                <div class="mt-6 flex justify-end">

                    <button class="px-6 py-2 bg-blue-600 rounded">
                        Save
                    </button>

                </div>

            </form>

        </div>
    </div>
</x-layouts.dashboard>