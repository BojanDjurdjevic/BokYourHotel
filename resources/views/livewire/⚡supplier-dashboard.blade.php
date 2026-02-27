<?php

use Livewire\Component;

new class extends Component
{
    public int $totalHotels = 4;
    public int $totalBookings = 128;
    public int $pending = 6;
    public float $revenue = 4320;

}; ?>

<div class="space-y-10">

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        

        <x-card>
            <p class="text-gray-400 text-sm">Total Hotels</p>
            <p class="text-2xl font-semibold text-white mt-2">
                {{ $totalHotels }}
            </p>
        </x-card>

        <x-card>
            <p class="text-gray-400 text-sm">Total Bookings</p>
            <p class="text-2xl font-semibold text-white mt-2">
                {{ $totalBookings }}
            </p>
        </x-card>

        <x-card>
            <p class="text-gray-400 text-sm">Revenue</p>
            <p class="text-2xl font-semibold text-white mt-2">
                €{{ $revenue }}
            </p>
        </x-card>

        <x-card>
            <p class="text-gray-400 text-sm">Pending</p>
            <p class="text-2xl font-semibold text-white mt-2">
                {{ $pending }}
            </p>
        </x-card>

    </div>

</div>