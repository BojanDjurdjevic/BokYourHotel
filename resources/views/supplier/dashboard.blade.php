<x-layouts.dashboard>
    <h1 class="text-3xl font-bold mb-6">Supplier dashboard</h1>
    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('supplier.hotels.index') }}" class="bg-gray-900 rounded-xl p-6">Your hotels <strong class="block text-3xl mt-2">{{ $hotelCount }}</strong></a>
        <a href="{{ route('supplier.pending') }}" class="bg-gray-900 rounded-xl p-6">Pending bookings <strong class="block text-3xl mt-2">{{ $pendingCount }}</strong></a>
        <a href="{{ route('supplier.bookings') }}" class="bg-gray-900 rounded-xl p-6">Confirmed bookings <strong class="block text-3xl mt-2">{{ $confirmedCount }}</strong></a>
    </div>
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('supplier.hotels.create') }}" class="px-5 py-3 bg-green-600 rounded-xl">Add hotel</a>
        <a href="{{ route('bookings.index') }}" class="px-5 py-3 bg-gray-800 rounded-xl">All accessible bookings</a>
    </div>
</x-layouts.dashboard>
