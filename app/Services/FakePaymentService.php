<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class FakePaymentService
{
    public function submit(Booking $booking, ?User $actor, int $attempt, string $outcome): Payment
    {
        abort_unless(config('payments.fake_enabled'), 403);
        abort_unless(in_array($outcome, ['success', 'failure'], true), 422);

        return DB::transaction(function () use ($booking, $actor, $attempt, $outcome) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->authorize($booking, $actor);
            $this->ensurePayable($booking);

            $payment = $booking->payment()->lockForUpdate()->first();

            if (! $payment) {
                if ($attempt !== 1) {
                    throw new BookingException('Please reload checkout before paying.');
                }

                $payment = $booking->payment()->create([
                    'amount' => $booking->total,
                    'currency' => $booking->currency,
                    'status' => PaymentStatus::Pending,
                    'reference' => (string) Str::uuid(),
                    'attempt' => 1,
                ]);
            }

            // Replaying an old form never settles a new attempt.
            if ($attempt !== $payment->attempt || $payment->status !== PaymentStatus::Pending) {
                return $payment;
            }

            $payment->update($outcome === 'success' ? [
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ] : [
                'status' => PaymentStatus::Failed,
                'failed_at' => now(),
            ]);

            if ($payment->status === PaymentStatus::Paid) {
                $booking->update(['locked_until' => null]);
            }

            return $payment;
        }, 3);
    }

    public function retry(Booking $booking, ?User $actor, int $attempt): Payment
    {
        abort_unless(config('payments.fake_enabled'), 403);

        return DB::transaction(function () use ($booking, $actor, $attempt) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $this->authorize($booking, $actor);
            $this->ensurePayable($booking);
            $payment = $booking->payment()->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::Failed && $payment->attempt === $attempt) {
                $payment->update([
                    'status' => PaymentStatus::Pending,
                    'attempt' => $payment->attempt + 1,
                ]);
            }

            return $payment;
        }, 3);
    }

    // Internal cancellation operation: caller holds the booking lock in this transaction.
    public function refundForCancellation(Booking $booking): void
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('Refund requires the booking cancellation transaction.');
        }

        $payment = $booking->payment()->lockForUpdate()->first();

        if ($payment?->status === PaymentStatus::Paid) {
            $payment->update([
                'status' => PaymentStatus::Refunded,
                'refunded_at' => now(),
                'refund_reference' => (string) Str::uuid(),
            ]);
        }
    }

    private function authorize(Booking $booking, ?User $actor): void
    {
        if ($actor) {
            Gate::forUser($actor)->authorize('pay', $booking);
        } else {
            // Null actors are only supplied by signed guest endpoints.
            abort_unless($booking->user_id === null, 403);
        }
    }

    private function ensurePayable(Booking $booking): void
    {
        if ($booking->holdDeadlinePassed() && ! $booking->payment()->where('status', PaymentStatus::Paid)->exists()) {
            throw new BookingException('The payment deadline has passed. Please create a new booking.');
        }
        if (! $booking->canBeCancelled() || now()->greaterThanOrEqualTo($booking->check_out)) {
            throw new BookingException('This booking can no longer be paid.');
        }
    }
}
