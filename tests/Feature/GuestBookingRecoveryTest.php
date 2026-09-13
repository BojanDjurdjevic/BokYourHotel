<?php

namespace Tests\Feature;

use App\Notifications\BookingNotice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\Support\CreatesBookingScenario;
use Tests\TestCase;

class GuestBookingRecoveryTest extends TestCase
{
    use RefreshDatabase, CreatesBookingScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scenario();
    }

    public function test_matching_guest_booking_sends_existing_signed_management_notice(): void
    {
        $booking = $this->reservation(true);
        Notification::fake();

        $this->from(route('guest.bookings.find'))
            ->post(route('guest.bookings.recover'), [
                'booking_number' => $booking->booking_number,
                'email' => strtoupper($booking->guest_email),
            ])
            ->assertRedirect(route('guest.bookings.find'))
            ->assertSessionHas('status', 'If the booking details match our records, we have sent a secure access link to the booking email address.');

        Notification::assertSentOnDemand(BookingNotice::class, function ($notice, $channels, $recipient) use ($booking) {
            $mail = $notice->toMail($recipient);
            $this->assertSame($booking->guest_email, $recipient->routes['mail']);
            $this->assertTrue(URL::hasValidSignature(\Illuminate\Http\Request::create($mail->viewData['manageUrl'])));
            $this->assertStringContainsString($booking->booking_number, $mail->viewData['manageUrl']);
            return true;
        });
    }

    public function test_missing_or_wrong_details_have_the_same_generic_response_and_send_nothing(): void
    {
        Notification::fake();
        $response = $this->from(route('guest.bookings.find'))->post(route('guest.bookings.recover'), [
            'booking_number' => 'missing-booking',
            'email' => 'guest@example.test',
        ]);
        $response->assertRedirect(route('guest.bookings.find'))->assertSessionHas('status', 'If the booking details match our records, we have sent a secure access link to the booking email address.');

        $response = $this->from(route('guest.bookings.find'))->post(route('guest.bookings.recover'), [
            'booking_number' => 'missing-booking',
            'email' => 'wrong@example.test',
        ]);
        $response->assertRedirect(route('guest.bookings.find'))->assertSessionHas('status', 'If the booking details match our records, we have sent a secure access link to the booking email address.');
        Notification::assertNothingSent();
    }

    public function test_authenticated_booking_cannot_be_recovered_as_a_guest(): void
    {
        $booking = $this->reservation();
        Notification::fake();

        $this->from(route('guest.bookings.find'))->post(route('guest.bookings.recover'), [
            'booking_number' => $booking->booking_number,
            'email' => $booking->guest_email,
        ])->assertRedirect(route('guest.bookings.find'));

        Notification::assertNothingSent();
    }

    public function test_recovery_is_rate_limited(): void
    {
        $ip = '198.51.100.44';
        RateLimiter::clear('guest-recovery:'.$ip);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('guest.bookings.recover'), [
                    'booking_number' => 'missing-'.$attempt,
                    'email' => 'guest@example.test',
                ])->assertRedirect();
        }

        $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('guest.bookings.recover'), [
                'booking_number' => 'missing-final',
                'email' => 'guest@example.test',
            ])->assertTooManyRequests();
    }
}
