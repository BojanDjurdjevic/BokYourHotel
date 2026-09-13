@props([
    'images' => [],
    'label' => 'Image gallery',
    'gridClass' => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3',
    'imageClass' => 'h-64 w-full rounded-2xl object-cover',
])

@php($images = collect($images)->values()->all())

<div x-data="imageLightbox(@js($images))" @keydown.escape.window="if (open) closeGallery()" @keydown.arrow-right.window="next()" @keydown.arrow-left.window="previous()">
    <div class="{{ $gridClass }}">
        @foreach($images as $index => $image)
            <button type="button" class="group rounded-2xl text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:ring-offset-2 focus-visible:ring-offset-gray-950" @click="openGallery({{ $index }})" aria-label="Open {{ $image['alt'] ?? 'image' }} {{ $index + 1 }} of {{ count($images) }}">
                <img src="{{ $image['src'] }}" alt="{{ $image['alt'] ?? 'Image' }}" class="{{ $imageClass }} transition group-hover:opacity-90" loading="lazy">
            </button>
        @endforeach
    </div>

    <div x-cloak x-show="open" x-transition.opacity x-bind:aria-hidden="open ? 'false' : 'true'" class="fixed inset-0 z-[100] bg-black/90 px-4 py-4 sm:px-6 sm:py-6" role="dialog" aria-modal="true" aria-label="{{ $label }}" @click.self="closeGallery()" @keydown.tab.prevent="trapFocus($event)">
        <div class="flex h-full w-full flex-col items-center justify-center gap-4" @click.stop>
            <div class="flex w-full max-w-7xl items-center justify-between text-sm text-gray-200">
                <p x-text="(currentIndex + 1) + ' / ' + images.length"></p>
                <button type="button" x-ref="closeButton" @click="closeGallery()" class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400" aria-label="Close image gallery"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="relative flex min-h-0 w-full max-w-7xl flex-1 items-center justify-center">
                <button type="button" x-show="images.length > 1" @click="previous()" class="absolute left-0 z-10 inline-flex h-11 w-11 items-center justify-center rounded-full bg-black/60 text-3xl text-white transition hover:bg-black/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 sm:left-2" aria-label="Previous image"><span aria-hidden="true">&#8249;</span></button>
                <img x-show="currentImage()" x-bind:src="currentImage()?.src" x-bind:alt="currentImage()?.alt || 'Image'" class="max-h-[calc(100vh-10rem)] max-w-full rounded-lg object-contain" @click.stop>
                <button type="button" x-show="images.length > 1" @click="next()" class="absolute right-0 z-10 inline-flex h-11 w-11 items-center justify-center rounded-full bg-black/60 text-3xl text-white transition hover:bg-black/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 sm:right-2" aria-label="Next image"><span aria-hidden="true">&#8250;</span></button>
            </div>
        </div>
    </div>
</div>

@once
<script>
function imageLightbox(images = []) {
    return {
        images,
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
            if (!this.images.length) return;
            this.lastFocused = document.activeElement;
            this.currentIndex = Math.max(0, Math.min(index, this.images.length - 1));
            this.open = true;
        },
        closeGallery() { this.open = false; },
        next() {
            if (!this.open || this.images.length < 2) return;
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        },
        previous() {
            if (!this.open || this.images.length < 2) return;
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        },
        currentImage() { return this.images[this.currentIndex] || null; },
        trapFocus(event) {
            const buttons = [...this.$el.querySelectorAll('button:not([disabled])')];
            if (!buttons.length) return;
            const first = buttons[0];
            const last = buttons[buttons.length - 1];
            if (event.shiftKey && document.activeElement === first) last.focus();
            else if (!event.shiftKey && document.activeElement === last) first.focus();
        },
    };
}
</script>
@endonce
