@php($layout = auth()->user()?->isSupplier() ? 'layouts.dashboard' : 'app-layout')
<x-dynamic-component :component="$layout">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">{{ $title ?? (auth()->user()->isAdmin() || auth()->user()->isSupplier() || auth()->user()->isSuperAdmin() ? 'Bookings' : 'My bookings') }}</h1>

        <div class="space-y-4">
            @forelse($bookings as $booking)
                <a href="{{ route('bookings.show', $booking) }}"
                   class="block bg-gray-900 border border-gray-800 rounded-2xl p-6 hover:border-gray-600">
                    <div class="flex flex-wrap justify-between gap-3">
                        <span class="font-semibold">{{ $booking->booking_number }}</span>
                        <span class="text-sm text-gray-400">{{ ucfirst($booking->status->value) }}</span>
                    </div>
                    <p class="mt-2">{{ \App\Support\PublicLabel::clean($booking->hotel->name, 'Hotel') }}</p>
                    <p class="text-sm text-gray-400">Payment: {{ ucfirst($booking->payment?->status->value ?? 'not started') }} (simulation)</p>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ $booking->check_in->format('d.m.Y') }} – {{ $booking->check_out->format('d.m.Y') }}
                    </p>
                </a>
            @empty
                <p class="text-gray-400">No bookings to display.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $bookings->links() }}</div>
    </div>
</x-dynamic-component>
