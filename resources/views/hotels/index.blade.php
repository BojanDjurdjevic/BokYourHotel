<x-app-layout>
    <h1 class="text-3xl font-bold mb-6">Find your hotel</h1>
    <form method="GET" action="{{ route('hotels.index') }}" class="space-y-4 mb-8" x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }" @pageshow.window="submitting = false">
        @include('hotels._search')
        @include('hotels._filters')
        @foreach($errors->all() as $error)<p class="text-red-400" role="alert">{{ $error }}</p>@endforeach
        <button :disabled="submitting" class="px-5 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl disabled:opacity-50">Search hotels</button>
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
                    <p>{{ $hotel->star_rating ? $hotel->star_rating.' stars' : 'Not rated' }}</p>
                    @if($hotel->search_price !== null)<p>From EUR {{ number_format($hotel->search_price, 2) }} {{ request('check_in') ? 'for the stay' : 'per night (indicative)' }}</p>@else<p>Availability not listed yet.</p>@endif
                    <a href="{{ route('hotels.show', ['hotel' => $hotel] + request()->query()) }}" class="inline-block text-blue-400 hover:text-blue-300">View hotel and availability</a>
                </div>
            </article>
        @empty
            <p class="text-gray-400">No published hotels match your search. Try another city.</p>
        @endforelse
    </div>
    {{ $hotels->links() }}
</x-app-layout>
