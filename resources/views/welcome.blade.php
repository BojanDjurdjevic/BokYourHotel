<x-app-layout>
    <section class="max-w-4xl mx-auto py-16 space-y-6">
        <p class="text-blue-400 font-semibold">BookYourHotel</p>
        <h1 class="text-4xl md:text-5xl font-bold">Find a place for your next stay</h1>
        <p class="text-xl text-gray-400">Explore hotels, choose your rooms and book with or without an account.</p>
        <form method="GET" action="{{ route('hotels.index') }}" class="space-y-4">
            @include('hotels._search')
            <button type="submit" class="min-h-12 rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-500">Explore hotels</button>
            <a href="{{ route('guest.bookings.find') }}" class="ml-3 inline-flex min-h-12 items-center rounded-xl border border-gray-700 px-5 py-3 text-gray-200 hover:border-gray-500 hover:text-white">Find my booking</a>
        </form>
        <p class="text-sm text-gray-500">Checkout uses simulated payments; no real money is charged.</p>
    </section>
</x-app-layout>
