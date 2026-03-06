<x-layouts.dashboard>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-white">My Hotels</h1>
        <x-button
            wire:click="$emit('openCreateHotel')"
        >
            Add New Hotel
        </x-button>
    </div>

    <div class="grid grid-cols-1  gap-6">
        {{-- Primer hotela, možeš u budućnosti foreach na $hotels --}}
        @foreach(range(1,6) as $hotel)
            <a href="#" class="block rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition">
                <div class="flex flex-col md:flex-row bg-gray-800 hover:bg-gray-700">
                    
                    {{-- Slika --}}
                    <div class="md:w-1/3 h-40 md:h-auto bg-gray-600 flex items-center justify-center text-gray-400 font-semibold text-xl">
                        Image
                    </div>

                    {{-- Info --}}
                    <div class="md:w-2/3 p-4 flex flex-col md:flex-row justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-white mb-2">Hotel Name {{ $hotel }}</h2>
                            <p class="text-gray-300 text-sm mb-1">City: Example City</p>
                            <p class="text-gray-300 text-sm mb-1">Board type: Breakfast included</p>
                            <p class="text-gray-300 text-sm mb-1">Rooms: 12 | Available: 8</p>
                        </div>
                        <div class="mt-4 flex md:flex-col items-center justify-between">
                            <span class="text-sm text-gray-400">Updated 3 days ago</span>
                            <span class="text-sm text-emerald-400 font-semibold">Active</span>
                        </div>
                    </div>

                </div>
            </a>
        @endforeach
    </div>
</x-layouts.dashboard>