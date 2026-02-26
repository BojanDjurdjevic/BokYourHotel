<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

        <x-card>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-400 text-sm">Total Bookings</p>
                    <p class="text-2xl font-semibold text-white mt-2">128</p>
                </div>
                <div class="text-indigo-400 text-2xl">📈</div>
            </div>
        </x-card>

        <x-card>
            <div>
                <p class="text-gray-400 text-sm">Revenue</p>
                <p class="text-2xl font-semibold text-white mt-2">€4,320</p>
            </div>
        </x-card>

    </div>
</x-app-layout>
