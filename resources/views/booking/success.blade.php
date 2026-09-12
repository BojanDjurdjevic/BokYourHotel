<x-app-layout>

    <div class="max-w-2xl mx-auto py-8">
        <div class="bg-gray-900 rounded-2xl p-6 md:p-8 shadow border border-gray-800">

            <p class="text-sm text-green-400 mb-2">
                Booking received
            </p>

            <h1 class="text-3xl font-bold mb-4">
                Thank you for your booking
            </h1>

            <p class="text-gray-400 mb-6">
                Your booking has been saved. Keep your booking number for reference.
            </p>

            <dl class="rounded-xl bg-gray-800 p-5 space-y-5">
                <div>
                    <dt class="text-sm text-gray-400">Booking number</dt>
                    <dd class="text-xl font-semibold mt-1 break-all">
                        {{ $booking->booking_number }}
                    </dd>
                </div>

                <div>
                    <dt class="text-sm text-gray-400">Status</dt>
                    <dd class="font-medium mt-1">
                        {{ ucfirst($booking->status->value) }}
                    </dd>
                </div>
            </dl>

            @if($booking->isPending())
                <p class="text-sm text-gray-400 mt-5">
                    Your booking is awaiting confirmation.
                </p>
            @endif

            <div class="mt-8">
                @if($booking->user_id === null && now()->lessThanOrEqualTo($booking->check_out->copy()->endOfDay()))
                    <a href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('guest.bookings.show', $booking->check_out->copy()->endOfDay(), $booking) }}"
                       class="inline-block px-6 py-3 mb-3 rounded-xl bg-green-600 hover:bg-green-500 font-semibold">
                        Manage booking
                    </a>
                @elseif(auth()->check())
                    @can('view', $booking)
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="inline-block px-6 py-3 mb-3 rounded-xl bg-green-600 hover:bg-green-500 font-semibold">
                            Manage booking
                        </a>
                    @endcan
                @endif

                <a href="{{ url('/') }}"
                   class="inline-block px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 font-semibold">
                    Back to home
                </a>
            </div>

        </div>
    </div>

</x-app-layout>
