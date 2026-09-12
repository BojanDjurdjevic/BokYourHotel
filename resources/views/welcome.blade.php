<x-app-layout>
    <section class="max-w-4xl mx-auto py-16 space-y-6">
        <p class="text-blue-400 font-semibold">BookYourHotel</p>
        <h1 class="text-4xl md:text-5xl font-bold">Find a place for your next stay</h1>
        <p class="text-xl text-gray-400">Explore hotels, choose your rooms and book with or without an account.</p>
        <form method="GET" action="{{ route('hotels.index') }}" class="space-y-4">
            @include('hotels._search')
            <button class="px-6 py-3 bg-blue-600 rounded-xl">Explore hotels</button>
        </form>
        <p class="text-sm text-gray-500">Portfolio demo. Checkout uses simulated payments; no real money is charged.</p>
    </section>
</x-app-layout>
