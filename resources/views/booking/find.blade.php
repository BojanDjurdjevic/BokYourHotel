<x-app-layout>
    <div class="mx-auto max-w-xl space-y-6">
        <div>
            <a href="{{ route('hotels.index') }}" class="text-sm text-blue-400 hover:text-blue-300">Back to hotels</a>
            <h1 class="mt-4 text-3xl font-bold">Find my booking</h1>
            <p class="mt-2 text-gray-400">Enter the booking number and email address used for the reservation. We will email a secure access link if the details match.</p>
        </div>

        @if(session('status'))
            <div class="rounded-xl border border-green-700/60 bg-green-950/40 p-4 text-green-200" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('guest.bookings.recover') }}" class="space-y-5 rounded-2xl border border-gray-800 bg-gray-900 p-6" x-data="{ submitting: false }" @submit="if (submitting) { $event.preventDefault(); } else { submitting = true; }">
            @csrf
            <div>
                <label for="booking_number" class="mb-2 block text-sm font-medium text-gray-200">Booking number</label>
                <input id="booking_number" name="booking_number" type="text" value="{{ old('booking_number') }}" required maxlength="64" autocomplete="off" placeholder="e.g. BYH-12345" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-base text-white placeholder:text-gray-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                <x-input-error :messages="$errors->get('booking_number')" class="mt-2" />
            </div>
            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-gray-200">Booking email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email" placeholder="you@example.com" class="min-h-12 w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-base text-white placeholder:text-gray-500 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400/50">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <button type="submit" :disabled="submitting" class="min-h-12 w-full rounded-xl bg-blue-600 px-4 py-3 text-base font-semibold text-white transition hover:bg-blue-500 disabled:cursor-wait disabled:opacity-60">
                <span x-show="!submitting">Email secure access link</span>
                <span x-show="submitting" x-cloak>Sending…</span>
            </button>
        </form>

        <p class="text-center text-sm text-gray-400">Have an account? <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300">Log in</a> to view your bookings.</p>
    </div>
</x-app-layout>
