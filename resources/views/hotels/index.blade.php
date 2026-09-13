<x-app-layout>
    <h1 class="text-3xl font-bold mb-6">Find your hotel</h1>
    <form method="GET" action="{{ route('hotels.index') }}" class="mb-8 space-y-4" x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }" @pageshow.window="submitting = false">
        @include('hotels._search')
        @include('hotels._filters')
        @foreach($errors->all() as $error)<p class="text-red-400" role="alert">{{ $error }}</p>@endforeach
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" :disabled="submitting" class="min-h-12 rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-500 disabled:cursor-wait disabled:opacity-50">Search hotels</button>
            <a href="{{ route('hotels.index') }}" class="inline-flex min-h-12 items-center rounded-xl border border-gray-700 px-5 py-3 text-gray-200 hover:border-gray-500 hover:text-white">Clear filters</a>
            <a href="{{ route('guest.bookings.find') }}" class="inline-flex min-h-12 items-center rounded-xl px-3 py-3 text-blue-400 hover:text-blue-300">Find my booking</a>
        </div>
        <x-input-error :messages="$errors->get('city')" />
    </form>
    <div id="hotel-results" class="scroll-mt-24 grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @forelse($hotels as $hotel)
            <article class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                @if($hotel->featuredImage)
                    <img src="{{ asset('storage/'.$hotel->featuredImage->path) }}" alt="{{ \App\Support\PublicLabel::clean($hotel->name, 'Hotel') }}" class="h-48 w-full object-cover" loading="lazy">
                @else
                    <div class="h-48 bg-gray-800 flex items-center justify-center text-gray-400">Hotel photos coming soon</div>
                @endif
                <div class="p-6 space-y-3">
                    <h2 class="text-xl font-semibold">{{ \App\Support\PublicLabel::clean($hotel->name, 'Hotel') }}</h2>
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
    @if(request()->query())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                requestAnimationFrame(() => document.getElementById('hotel-results')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
            });
        </script>
    @endif
</x-app-layout>
