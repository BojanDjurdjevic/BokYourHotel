<x-layouts.dashboard>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-white">My Bookings</h1>
        <button
            class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded shadow"
            wire:click="$emit('openCreateBooking')"
        >
            Add New Booking
        </button>
    </div>

    <div class="space-y-6">
        {{-- Primer rezervacija, možeš foreach na $bookings --}}
        @foreach(range(1,5) as $booking)
            <div class="flex flex-col lg:flex-row bg-gray-800 rounded-xl overflow-hidden shadow hover:shadow-2xl transition">
                
                {{-- Guest info --}}
                <div class="flex-1 p-4 flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-white mb-2">Guest Name {{ $booking }}</h2>
                        <p class="text-gray-300 text-sm mb-1">Hotel: Example Hotel {{ $booking }}</p>
                        <p class="text-gray-300 text-sm mb-1">Check-in: 2026-03-10</p>
                        <p class="text-gray-300 text-sm mb-1">Check-out: 2026-03-15</p>
                        <p class="text-gray-300 text-sm mb-1">Rooms: 2</p>
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm text-gray-400">Booking ID: #{{ 1000 + $booking }}</span>
                        <span class="text-sm text-emerald-400 font-semibold">Confirmed</span>
                    </div>
                </div>

                {{-- Optional side actions --}}
                <div class="p-4 flex items-center justify-end space-x-2 border-t lg:border-t-0 lg:border-l border-gray-700">
                    <a href="#">
                        <x-secondary-button>
                            Edit
                        </x-secondary-button>
                    </a>
                    <x-danger-button
                        wire:click="$emit('openCancelBooking', {{ $booking }})"
                    >
                        Cancel
                    </x-danger-button>
                </div>

            </div>
        @endforeach
    </div>
</x-layouts.dashboard>