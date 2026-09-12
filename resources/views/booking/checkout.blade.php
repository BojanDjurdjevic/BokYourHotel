<x-app-layout>
    <div class="max-w-2xl mx-auto bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-6">
        <h1 class="text-3xl font-bold">Fake checkout</h1>
        @include('booking._deadline')
        <p class="text-amber-300">Development simulation only. No money is charged. Never enter card details.</p>
        <dl class="space-y-3">
            <div><dt class="text-gray-400">Booking number</dt><dd>{{ $booking->booking_number }}</dd></div>
            <div><dt class="text-gray-400">Booking status</dt><dd>{{ ucfirst($booking->status->value) }}</dd></div>
            <div><dt class="text-gray-400">Total</dt><dd>{{ number_format($booking->total, 2) }} {{ $booking->currency }}</dd></div>
            <div><dt class="text-gray-400">Payment status</dt><dd>{{ ucfirst($booking->payment?->status->value ?? 'not started') }}</dd></div>
        </dl>
        @if($booking->isPending())
            <p class="text-gray-400">Your booking is awaiting confirmation. Payment does not confirm your stay.</p>
        @endif
        @if($booking->payment?->status === \App\Enums\PaymentStatus::Paid)
            <p class="text-green-400">Simulated payment successful.</p>
        @elseif($booking->payment?->status === \App\Enums\PaymentStatus::Failed)
            <p class="text-red-400">Simulated payment failed. Start a new attempt to try again.</p>
        @elseif($booking->payment?->status === \App\Enums\PaymentStatus::Refunded)
            <p class="text-gray-300">Payment was refunded in this simulation.</p>
        @endif
        @if(config('payments.fake_enabled') && $booking->canBeCancelled() && ! $booking->holdDeadlinePassed() && now()->lessThan($booking->check_out))
            @if(! $booking->payment || $booking->payment->status === \App\Enums\PaymentStatus::Pending)
                <form method="POST" action="{{ $submitUrl }}" class="flex flex-wrap gap-3" x-data="{ submitting: false, outcome: '' }"
                      @submit="if (submitting) { $event.preventDefault() } else { submitting = true }">
                    @csrf
                    <input type="hidden" name="attempt" value="{{ $attempt }}">
                    <input type="hidden" name="outcome" :value="outcome">
                    <button @click="outcome = 'success'" :disabled="submitting" class="rounded-xl px-5 py-3 bg-green-600 hover:bg-green-500 disabled:opacity-50">Simulate successful payment</button>
                    <button @click="outcome = 'failure'" :disabled="submitting" class="rounded-xl px-5 py-3 bg-red-700 hover:bg-red-600 disabled:opacity-50">Simulate failed payment</button>
                </form>
            @elseif($booking->payment->status === \App\Enums\PaymentStatus::Failed)
                <form method="POST" action="{{ $retryUrl }}">
                    @csrf
                    <input type="hidden" name="attempt" value="{{ $attempt }}">
                    <button class="rounded-xl px-5 py-3 bg-blue-600 hover:bg-blue-500">Start new payment attempt</button>
                </form>
            @endif
        @elseif(! config('payments.fake_enabled'))
            <p class="text-amber-300">Fake payments are disabled in this environment.</p>
        @endif
        <x-input-error :messages="$errors->all()" />
        <div class="flex flex-wrap gap-5">
            <a href="{{ $successUrl }}" class="text-blue-400 hover:text-blue-300">Booking receipt</a>
            <a href="{{ $manageUrl }}" class="text-blue-400 hover:text-blue-300">Manage booking</a>
        </div>
    </div>
</x-app-layout>
