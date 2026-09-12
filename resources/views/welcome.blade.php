<x-app-layout>
    <section class="max-w-4xl mx-auto py-16 space-y-6">
        <p class="text-blue-400 font-semibold">BookYourHotel</p>
        <h1 class="text-4xl md:text-5xl font-bold">Find a place for your next stay</h1>
        <p class="text-xl text-gray-400">Explore hotels, choose your rooms and book with or without an account.</p>
        <a href="{{ route('hotels.index') }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl font-semibold">Explore hotels</a>
        <p class="text-sm text-gray-500">Portfolio demo. Checkout uses simulated payments; no real money is charged.</p>
    </section>
</x-app-layout>
