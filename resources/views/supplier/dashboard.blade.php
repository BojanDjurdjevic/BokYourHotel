<x-layouts.dashboard>
    {{-- Revenue & Metrics --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <x-card>
            <p class="text-gray-400 text-sm">Total Hotels</p>
            <p class="text-2xl font-semibold text-white mt-2">Total Hotels</p>
        </x-card>

        <x-card>
            <p class="text-gray-400 text-sm">Total Bookings</p>
            <p class="text-2xl font-semibold text-white mt-2">Total Bookings</p>
        </x-card>

        <x-card>
            <p class="text-gray-400 text-sm">Revenue (Month)</p>
            <p class="text-2xl font-semibold text-white mt-2">€ 100</p>
        </x-card>

        <x-card>
            <p class="text-gray-400 text-sm">Pending Bookings</p>
            <p class="text-2xl font-semibold text-white mt-2">Pendings</p>
        </x-card>
    </div>

    {{-- Quick Links / Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
        <a href="#"
            class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-xl shadow text-center">
            View Hotels
        </a>

        <a href="#"
            class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-xl shadow text-center">
            Confirmed Bookings
        </a>

        <a href="#"
            class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-xl shadow text-center">
            Pending Bookings
        </a>

        <button wire:click="$emit('openCreateHotel')"
            class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-3 rounded-xl shadow text-center">
            Add New Hotel
        </button>

        <button wire:click="$emit('openCreateBooking')"
            class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-3 rounded-xl shadow text-center">
            Add New Booking
        </button>
    </div>

    {{-- Opcionalno: najnovije rezervacije / hotels preview --}}
    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-white mb-4">Latest Bookings</h2>
        @foreach(range(1,5) as $booking)
            <div class="flex flex-col lg:flex-row bg-gray-800 rounded-xl overflow-hidden shadow hover:shadow-2xl transition p-4">
                <div class="flex-1">
                    <p class="text-white font-semibold">Guest name</p>
                    <p class="text-gray-300 text-sm">Hotel: Hotel name</p>
                    <p class="text-gray-300 text-sm">Status: 
                        <span class="{{ 'pending' === 'pending' ? 'text-yellow-400' : 'text-emerald-400' }}">
                            {{ ucfirst('Booking status') }}
                        </span>
                    </p>
                </div>
                <div class="flex items-center justify-end space-x-2 mt-4 lg:mt-0">
                    <a href="#"
                        class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded text-sm">
                        Edit
                    </a>
                    @if('pending' === 'pending')
                        <button wire:click="$emit('approveBooking')"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1 rounded text-sm">
                            Approve
                        </button>
                        <button wire:click="$emit('rejectBooking')"
                            class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-sm">
                            Reject
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.dashboard>