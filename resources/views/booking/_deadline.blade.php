@if($booking->isPending() && $booking->locked_until && $booking->payment?->status !== \App\Enums\PaymentStatus::Paid)
    <p class="text-amber-300">
        @if($booking->holdDeadlinePassed())
            Payment deadline passed. This reservation cannot be paid; please make a new booking.
        @else
            Pay by {{ $booking->locked_until->format('d.m.Y H:i:s') }} ({{ config('app.timezone') }}) to keep this reservation.
        @endif
    </p>
@endif
