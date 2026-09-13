<x-app-layout>
    <div class="space-y-8">
        <a href="{{ route('hotels.index', request()->query()) }}" class="text-blue-400">Back to hotels</a>
        <div><h1 class="text-3xl font-bold">{{ $hotel->name }}</h1><p class="mt-2 text-gray-400">{{ $hotel->address }}, {{ $hotel->city }}, {{ $hotel->country }}</p></div>
        @if($hotel->images->isNotEmpty())
            @php
                $galleryImages = $hotel->images->values()->map(fn ($image, $index) => [
                    'src' => asset('storage/'.$image->path),
                    'alt' => $hotel->name.' hotel image '.($index + 1),
                ])->values()->all();
            @endphp
            <div
                x-data="{
                    images: @js($galleryImages),
                    open: false,
                    currentIndex: 0,
                    lastFocused: null,
                    init() {
                        this.$watch('open', (isOpen) => {
                            document.body.style.overflow = isOpen ? 'hidden' : '';
                            if (isOpen) {
                                this.$nextTick(() => this.$refs.closeButton?.focus());
                            } else if (this.lastFocused) {
                                this.$nextTick(() => this.lastFocused?.focus());
                            }
                        });
                    },
                    openGallery(index) {
                        this.lastFocused = document.activeElement;
                        this.currentIndex = index;
                        this.open = true;
                    },
                    closeGallery() {
                        this.open = false;
                    },
                    next() {
                        if (!this.open || this.images.length < 2) return;
                        this.currentIndex = (this.currentIndex + 1) % this.images.length;
                    },
                    previous() {
                        if (!this.open || this.images.length < 2) return;
                        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                    },
                    currentImage() {
                        return this.images[this.currentIndex] || null;
                    }
                }"
                @keydown.escape.window="if (open) closeGallery()"
                @keydown.arrow-right.window="next()"
                @keydown.arrow-left.window="previous()"
            >
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($hotel->images->values() as $index => $image)
                        <button
                            type="button"
                            class="group text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950 rounded-2xl"
                            @click="openGallery({{ $index }})"
                            aria-label="Open {{ $hotel->name }} image {{ $index + 1 }} of {{ $hotel->images->count() }}"
                        >
                            <img src="{{ asset('storage/'.$image->path) }}" alt="{{ $hotel->name }} hotel image {{ $index + 1 }}" class="w-full h-64 object-cover rounded-2xl transition group-hover:opacity-90" loading="lazy">
                        </button>
                    @endforeach
                </div>

                <div
                    x-cloak
                    x-show="open"
                    x-transition.opacity
                    x-bind:aria-hidden="open ? 'false' : 'true'"
                    class="fixed inset-0 z-[100] bg-black/90 px-4 py-4 sm:px-6 sm:py-6"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Hotel image gallery"
                    @click.self="closeGallery()"
                >
                    <div class="flex h-full w-full flex-col items-center justify-center gap-4" @click.stop>
                        <div class="flex w-full max-w-7xl items-center justify-between text-sm text-gray-200">
                            <p x-text="(currentIndex + 1) + ' / ' + images.length"></p>
                            <button
                                type="button"
                                x-ref="closeButton"
                                @click="closeGallery()"
                                class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                                aria-label="Close image gallery"
                            >
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="relative flex min-h-0 w-full max-w-7xl flex-1 items-center justify-center">
                            <button
                                type="button"
                                x-show="images.length > 1"
                                @click="previous()"
                                class="absolute left-0 z-10 inline-flex h-11 w-11 items-center justify-center rounded-full bg-black/60 text-3xl text-white transition hover:bg-black/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 sm:left-2"
                                aria-label="Previous image"
                            >
                                <span aria-hidden="true">&#8249;</span>
                            </button>

                            <img
                                x-show="currentImage()"
                                x-bind:src="currentImage()?.src"
                                x-bind:alt="currentImage()?.alt || 'Hotel image'"
                                class="max-h-[calc(100vh-10rem)] max-w-full rounded-lg object-contain"
                                @click.stop
                            >

                            <button
                                type="button"
                                x-show="images.length > 1"
                                @click="next()"
                                class="absolute right-0 z-10 inline-flex h-11 w-11 items-center justify-center rounded-full bg-black/60 text-3xl text-white transition hover:bg-black/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 sm:right-2"
                                aria-label="Next image"
                            >
                                <span aria-hidden="true">&#8250;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <p class="text-gray-300 whitespace-pre-line">{{ $hotel->description }}</p>
        <a href="{{ route('booking.show', ['hotel' => $hotel] + request()->only('check_in','check_out','adults','children')) }}" class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-500 rounded-xl">Check availability and book</a>
        <h2 class="text-2xl font-semibold">Rooms</h2>
        <p class="text-gray-400">Choose dates to see current availability and the final price for your stay.</p>
        <div class="grid md:grid-cols-2 gap-6">
            @forelse($hotel->rooms as $room)
                <article class="p-6 bg-gray-900 border border-gray-800 rounded-2xl space-y-3">
                    @if($room->featuredImage)
                        <img src="{{ asset('storage/'.$room->featuredImage->path) }}" alt="{{ $room->name }}" class="h-48 w-full object-cover rounded-xl" loading="lazy">
                    @endif
                    <h3 class="text-xl font-semibold">{{ $room->name }}</h3>
                    <p class="text-gray-400">Up to {{ $room->capacity }} guests per room</p>
                    <p>{{ $room->description }}</p>
                    <p class="text-sm text-gray-400">{{ $room->facilities->pluck('name')->join(' · ') }}</p>
                    <p class="text-sm">Board options: {{ $room->boardTypes->pluck('name')->join(', ') ?: 'Not available yet' }}</p>
                </article>
            @empty
                <p class="text-gray-400">Rooms are not available yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
