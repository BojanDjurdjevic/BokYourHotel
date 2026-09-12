<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomInventory;
use App\Models\User;
use App\Services\BookingService;
use App\Services\FakePaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private Booking $booking;
    private User $owner;
    private User $supplier;
    private Hotel $hotel;
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-09-15 14:00:00'));
        config(['payments.fake_enabled' => true]);
        $this->owner = User::factory()->create();
        $this->supplier = User::factory()->create(['role' => 'supplier']);
        $this->hotel = Hotel::create([
            'supplier_id' => $this->supplier->id, 'name' => 'Payment Hotel',
            'city' => 'Belgrade', 'country' => 'Serbia', 'address' => 'Test Street',
        ]);
        $this->hotel->forceFill(['published' => true])->save();
        $this->room = Room::create([
            'hotel_id' => $this->hotel->id,
            'room_type_id' => DB::table('room_types')->insertGetId(['name' => 'Double']),
            'bed_type_id' => DB::table('bed_types')->insertGetId(['name' => 'Double']),
            'name' => 'Double', 'capacity' => 2, 'total_units' => 3, 'price_per_night' => 100,
        ]);
        $board = DB::table('board_types')->insertGetId(['code' => 'BB', 'name' => 'Breakfast']);
        $this->room->boardTypes()->attach($board, ['price' => 10]);
        $this->actingAs($this->owner);
        $this->booking = app(BookingService::class)->create([
            'hotel_id' => $this->hotel->id, 'check_in' => '2026-09-18', 'check_out' => '2026-09-20',
            'guest_name' => 'Private Guest', 'guest_email' => 'private@example.com',
            'items' => [['room_id' => $this->room->id, 'board_type_id' => $board, 'quantity' => 2, 'adults' => 2]],
        ]);
    }

    private function pay(string $outcome = 'success', int $attempt = 1)
    {
        return $this->post(route('payments.submit', $this->booking), compact('outcome', 'attempt'));
    }

    private function guest(): void
    {
        $this->booking->update(['user_id' => null]);
        auth()->forgetUser();
    }

    private function guestUrl(string $action, int $attempt = 1): string
    {
        return URL::temporarySignedRoute('guest.payments.'.$action, now()->addDay(), [
            'booking' => $this->booking, 'attempt' => $attempt,
        ]);
    }

    public function test_checkout_get_is_read_only_and_post_uses_server_amount_and_currency(): void
    {
        $this->get(route('payments.show', $this->booking))->assertOk()->assertSee('440.00 EUR');
        $this->assertDatabaseCount('payments', 0);
        $this->post(route('payments.submit', $this->booking), [
            'outcome' => 'success', 'attempt' => 1, 'amount' => 1, 'currency' => 'USD',
            'status' => 'refunded', 'booking_id' => 999, 'user_id' => 999,
        ])->assertRedirect(route('payments.show', $this->booking));
        $payment = Payment::sole();
        $this->assertSame('440.00', $payment->amount);
        $this->assertSame('EUR', $payment->currency);
        $this->assertSame(PaymentStatus::Paid, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
        $this->get(route('payments.show', $this->booking))->assertSee('Simulated payment successful');
        $this->get(route('bookings.show', $this->booking))->assertSee('Paid');
    }

    public function test_duplicate_payment_and_opposite_outcome_cannot_change_settled_payment(): void
    {
        $this->pay()->assertRedirect();
        $original = Payment::sole()->toArray();
        $this->pay()->assertRedirect();
        $this->pay('failure')->assertRedirect();
        $this->assertDatabaseCount('payments', 1);
        $this->assertSame($original, Payment::sole()->toArray());
    }

    public function test_failure_requires_explicit_retry_and_stale_forms_cannot_pay_new_attempt(): void
    {
        $this->pay('failure')->assertRedirect();
        $this->pay()->assertRedirect();
        $this->assertSame(PaymentStatus::Failed, Payment::sole()->status);
        $retry = route('payments.retry', $this->booking);
        $this->post($retry, ['attempt' => 1])->assertRedirect();
        $this->post($retry, ['attempt' => 1])->assertRedirect();
        $this->assertSame(2, Payment::sole()->attempt);
        $this->pay()->assertRedirect();
        $this->assertSame(PaymentStatus::Pending, Payment::sole()->status);
        $this->pay('success', 2)->assertRedirect();
        $this->assertSame(PaymentStatus::Paid, Payment::sole()->status);
        $this->assertNotNull(Payment::sole()->failed_at);
    }

    public function test_owner_cancellation_refunds_once_and_preserves_items(): void
    {
        $this->pay();
        $items = $this->booking->items()->get()->toArray();
        $cancel = route('bookings.cancel', $this->booking);
        $this->post($cancel)->assertSessionHas('success');
        $refund = Payment::sole()->toArray();
        $this->post($cancel)->assertSessionHas('error');
        $this->assertSame(PaymentStatus::Refunded, Payment::sole()->status);
        $this->assertNotNull(Payment::sole()->refund_reference);
        $this->assertSame($refund, Payment::sole()->toArray());
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
        $this->assertSame($items, $this->booking->items()->get()->toArray());
    }

    public function test_unpaid_cancellation_does_not_create_payment_or_refund(): void
    {
        $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('success');
        $this->assertDatabaseCount('payments', 0);
        $this->pay()->assertSessionHas('error');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_failed_payment_cancellation_does_not_record_refund(): void
    {
        $this->pay('failure');
        $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('success');
        $this->assertSame(PaymentStatus::Failed, Payment::sole()->status);
        $this->assertNull(Payment::sole()->refunded_at);
        $this->post(route('payments.retry', $this->booking), ['attempt' => 1])->assertSessionHas('error');
    }

    public function test_inventory_failure_rolls_back_refund_and_cancellation(): void
    {
        $this->pay();
        RoomInventory::whereDate('date', '2026-09-19')->delete();
        $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('error');
        $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
        $this->assertSame(PaymentStatus::Paid, Payment::sole()->status);
        $this->assertNull(Payment::sole()->refund_reference);
        $this->assertSame([1], RoomInventory::pluck('available')->all());
    }

    public function test_refund_write_failure_rolls_back_all_cancellation_changes(): void
    {
        $this->pay();
        Payment::updating(function (Payment $payment) {
            if ($payment->status === PaymentStatus::Refunded) throw new BookingException('Refund simulation unavailable.');
        });
        try {
            $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('error');
            $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
            $this->assertSame(PaymentStatus::Paid, Payment::sole()->status);
            $this->assertSame([1, 1], RoomInventory::orderBy('date')->pluck('available')->all());
        } finally {
            Payment::flushEventListeners();
        }
    }

    public function test_late_owner_cancellation_does_not_refund_but_supplier_cancellation_does(): void
    {
        $this->pay();
        $this->travelTo(Carbon::parse('2026-09-17 00:00:01'));
        $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('error');
        $this->assertSame(PaymentStatus::Paid, Payment::sole()->status);
        $this->actingAs($this->supplier)->post(route('bookings.cancel', $this->booking))->assertSessionHas('success');
        $this->assertSame(PaymentStatus::Refunded, Payment::sole()->status);
    }

    public function test_other_users_and_staff_cannot_pay_someone_elses_booking(): void
    {
        foreach ([User::factory()->create(), $this->supplier, User::factory()->create(['role' => 'admin'])] as $actor) {
            $this->actingAs($actor)->get(route('payments.show', $this->booking))->assertForbidden();
            $this->pay()->assertForbidden();
            $this->post(route('payments.retry', $this->booking), ['attempt' => 1])->assertForbidden();
        }
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_signed_guest_can_pay_manage_and_cancel_without_account(): void
    {
        $this->guest();
        $this->get($this->guestUrl('show'))->assertOk()->assertDontSee($this->booking->guest_email);
        $this->post($this->guestUrl('submit'), ['attempt' => 1, 'outcome' => 'success'])->assertRedirect();
        $this->get(URL::temporarySignedRoute('guest.bookings.show', now()->addDay(), $this->booking))->assertOk()->assertSee('Paid');
        $this->post(URL::temporarySignedRoute('guest.bookings.cancel', now()->addDay(), $this->booking))->assertSessionHas('success');
        $this->assertSame(PaymentStatus::Refunded, Payment::sole()->status);
    }

    public function test_invalid_expired_tampered_signatures_and_signed_owner_booking_are_rejected(): void
    {
        $this->get($this->guestUrl('show'))->assertForbidden();
        $this->guest();
        $this->get(route('guest.payments.show', $this->booking))->assertForbidden();
        $this->post(route('guest.payments.submit', $this->booking), ['attempt' => 1, 'outcome' => 'success'])->assertForbidden();
        $this->post($this->guestUrl('submit').'&amount=1', ['attempt' => 1, 'outcome' => 'success'])->assertForbidden();
        $expired = URL::temporarySignedRoute('guest.payments.show', now()->subSecond(), $this->booking);
        $this->get($expired)->assertForbidden();
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_guest_body_cannot_replace_signed_attempt(): void
    {
        $this->guest();
        $old = $this->guestUrl('submit');
        $this->post($old, ['attempt' => 1, 'outcome' => 'failure']);
        $this->post($this->guestUrl('retry'), ['attempt' => 1]);
        $this->post($old, ['attempt' => 2, 'outcome' => 'success']);
        $this->assertSame(PaymentStatus::Pending, Payment::sole()->status);
        $this->post($this->guestUrl('submit', 2), ['attempt' => 2, 'outcome' => 'success']);
        $this->assertSame(PaymentStatus::Paid, Payment::sole()->status);
    }

    public function test_terminal_bookings_and_disabled_simulator_cannot_be_paid(): void
    {
        foreach ([BookingStatus::Cancelled, BookingStatus::Completed, BookingStatus::Rejected, BookingStatus::Expired] as $status) {
            $this->booking->update(compact('status'));
            $this->pay()->assertSessionHas('error');
        }
        $this->booking->update(['status' => BookingStatus::Pending]);
        config(['payments.fake_enabled' => false]);
        $this->pay()->assertForbidden();
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payment_commands_are_validated_and_unique_booking_constraint_exists(): void
    {
        $this->postJson(route('payments.submit', $this->booking), ['status' => 'paid'])->assertUnprocessable();
        $this->pay('refunded')->assertSessionHasErrors('outcome');
        $this->pay();
        $this->expectException(\Illuminate\Database\QueryException::class);
        Payment::create(array_replace(Payment::sole()->getAttributes(), ['id' => 999, 'reference' => (string) \Illuminate\Support\Str::uuid()]));
    }

    public function test_signed_payment_posts_still_require_csrf(): void
    {
        $this->guest();
        $url = $this->guestUrl('submit');
        $this->app['env'] = 'local';
        try {
            $this->post($url, ['attempt' => 1, 'outcome' => 'success'])->assertStatus(419);
            $this->assertDatabaseCount('payments', 0);
        } finally {
            $this->app['env'] = 'testing';
        }
    }
}
