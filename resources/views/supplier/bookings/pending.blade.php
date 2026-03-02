<x-layouts.dashboard>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-white">Pending Bookings</h1>
    </div>

    <div class="space-y-6">
        @foreach(range(1,3) as $booking)
            <div class="flex flex-col lg:flex-row bg-gray-800 rounded-xl overflow-hidden shadow hover:shadow-2xl transition">
                
                {{-- Info --}}
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-white mb-2">Guest Name {{ $booking }}</h2>
                        <p class="text-gray-300 text-sm mb-1">Hotel: Example Hotel {{ $booking }}</p>
                        <p class="text-gray-300 text-sm mb-1">Check-in: 2026-03-20</p>
                        <p class="text-gray-300 text-sm mb-1">Check-out: 2026-03-25</p>
                        <p class="text-gray-300 text-sm mb-1">Rooms: 1</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-gray-400">Booking ID: #{{ 2000 + $booking }}</span>
                        <span class="text-sm text-yellow-400 font-semibold">Pending</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="p-4 flex items-center justify-end space-x-2 border-t lg:border-t-0 lg:border-l border-gray-700">
                    <x-button
                        wire:click="$emit('approveBooking', {{ $booking }})"
                    >
                        Approve
                    </x-button>
                    <x-danger-button
                        wire:click="$emit('rejectBooking', {{ $booking }})"
                    >
                        Reject
                    </x-danger-button>
                </div>

            </div>
        @endforeach
    </div>
</x-layouts.dashboard>