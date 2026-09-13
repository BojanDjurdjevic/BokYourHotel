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

            <livewire:supplier.room-images-manager :room="$room" />

        </div>
    </div>
</x-layouts.dashboard>
