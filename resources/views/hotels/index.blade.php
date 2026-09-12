<x-app-layout>
    <h1 class="text-3xl font-bold mb-6">Find your hotel</h1>
    <form method="GET" action="{{ route('hotels.index') }}" class="flex flex-wrap gap-3 mb-8">
        <label for="city" class="sr-only">City</label>
        <input id="city" name="city" value="{{ request('city') }}" maxlength="64" placeholder="Search by city" class="rounded-xl bg-gray-900 border-gray-700">
        <button class="px-5 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl">Search hotels</button>
        <a href="{{ route('hotels.index') }}" class="px-5 py-3 text-gray-300">Clear search</a>
        <x-input-error :messages="$errors->get('city')" />
    </form>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @forelse($hotels as $hotel)
            <article class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                @if($hotel->featuredImage)
                    <img src="{{ asset('storage/'.$hotel->featuredImage->path) }}" alt="{{ $hotel->name }}" class="w-full h-48 object-cover" loading="lazy">
                @else
                    <div class="h-48 bg-gray-800 flex items-center justify-center text-gray-400">Hotel photos coming soon</div>
                @endif
                <div class="p-6 space-y-3">
                    <h2 class="text-xl font-semibold">{{ $hotel->name }}</h2>
                    <p class="text-gray-400">{{ $hotel->city }}, {{ $hotel->country }}</p>
                    <a href="{{ route('hotels.show', $hotel) }}" class="inline-block text-blue-400 hover:text-blue-300">View hotel and availability</a>
                </div>
            </article>
        @empty
            <p class="text-gray-400">No published hotels match your search. Try another city.</p>
        @endforelse
    </div>
    {{ $hotels->links() }}
</x-app-layout>
