<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Exceptions\BookingException;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use App\Models\User;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class BookingManagementTest extends TestCase
{
    use RefreshDatabase;

    private Booking $booking;
    private User $owner;
    private User $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-09-15 14:00:00'));

        $this->owner = User::factory()->create(['role' => 'user']);
        $this->supplier = User::factory()->create(['role' => 'supplier']);
        $hotel = Hotel::create([
            'supplier_id' => $this->supplier->id, 'name' => 'Management Hotel',
            'city' => 'Belgrade', 'country' => 'Serbia', 'address' => 'Test Street',
        ]);
        $room = Room::create([
            'hotel_id' => $hotel->id,
            'room_type_id' => DB::table('room_types')->insertGetId(['name' => 'Double']),
            'bed_type_id' => DB::table('bed_types')->insertGetId(['name' => 'Double']),
            'name' => 'Double room', 'capacity' => 2, 'total_units' => 3, 'price_per_night' => 100,
        ]);
        $boards = [
            DB::table('board_types')->insertGetId(['code' => 'BB', 'name' => 'Breakfast']),
            DB::table('board_types')->insertGetId(['code' => 'HB', 'name' => 'Half board']),
        ];
        $room->boardTypes()->attach([$boards[0] => ['price' => 10], $boards[1] => ['price' => 20]]);

        $this->actingAs($this->owner);
        $this->booking = app(BookingService::class)->create([
            'hotel_id' => $hotel->id, 'check_in' => '2026-09-18', 'check_out' => '2026-09-20',
            'guest_name' => 'Booking Owner', 'guest_email' => 'owner@example.com',
            'items' => [
                ['room_id' => $room->id, 'board_type_id' => $boards[0], 'quantity' => 2, 'adults' => 2],
                ['room_id' => $room->id, 'board_type_id' => $boards[1], 'quantity' => 1, 'adults' => 1],
            ],
        ]);
    }

    private function guestBooking(): void
    {
        $this->booking->update(['user_id' => null]);
        auth()->forgetUser();
    }

    private function guestUrl(string $action): string
    {
        return URL::temporarySignedRoute('guest.bookings.' . $action, now()->addDays(5), $this->booking);
    }

    public function test_two_cancellation_requests_restore_inventory_once_and_keep_items(): void
    {
        $items = $this->booking->items()->get()->toArray();
        $url = route('bookings.cancel', $this->booking);
        $this->post($url, ['reason' => 'Plans changed'])->assertSessionHas('success');
        $this->post($url)->assertSessionHas('error');

        $this->assertSame(BookingStatus::Cancelled, $this->booking->refresh()->status);
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
        $this->assertSame($items, $this->booking->items()->get()->toArray());
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_stale_confirm_cannot_overwrite_cancellation(): void
    {
        $staleBooking = Booking::findOrFail($this->booking->id);
        app(BookingService::class)->cancel($this->booking, $this->owner);

        try {
            app(BookingService::class)->confirm($staleBooking, $this->supplier);
            $this->fail('Stale confirmation must fail.');
        } catch (BookingException $e) {
            $this->assertSame(BookingStatus::Cancelled, $this->booking->refresh()->status);
            $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
        }
    }

    public function test_cancellation_after_confirmation_uses_current_status(): void
    {
        $staleBooking = Booking::findOrFail($this->booking->id);
        app(BookingService::class)->confirm($this->booking, $this->supplier);
        app(BookingService::class)->cancel($staleBooking, $this->owner);

        $this->assertSame(BookingStatus::Cancelled, $this->booking->refresh()->status);
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_other_users_and_other_suppliers_cannot_view_or_change_booking(): void
    {
        foreach (['user', 'supplier'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('bookings.index'))->assertOk()->assertDontSee($this->booking->booking_number);
            $this->get(route('bookings.show', $this->booking))->assertForbidden();
            foreach (['cancel', 'confirm', 'complete'] as $action) {
                $this->post(route('bookings.' . $action, $this->booking))->assertForbidden();
            }
        }
        $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
    }

    public function test_owner_can_view_but_cannot_confirm_or_complete(): void
    {
        $this->get(route('bookings.index'))->assertOk()->assertSee($this->booking->booking_number);
        $this->get(route('bookings.show', $this->booking))->assertOk()->assertSee('Cancel booking');
        $this->post(route('bookings.confirm', $this->booking))->assertForbidden();
        $this->post(route('bookings.complete', $this->booking))->assertForbidden();
    }

    public function test_staff_can_confirm_and_cancel_even_after_owner_deadline(): void
    {
        foreach ([$this->supplier, User::factory()->create(['role' => 'admin']), User::factory()->create(['role' => 'superadmin'])] as $actor) {
            $this->booking->update(['status' => BookingStatus::Pending]);
            RoomInventory::query()->update(['available' => 0]);
            $this->actingAs($actor);
            $this->get(route('bookings.index'))->assertOk()->assertSee($this->booking->booking_number);
            $this->get(route('bookings.show', $this->booking))->assertOk();
            $this->travelTo(Carbon::parse('2026-09-18 12:00:00'));
            $this->post(route('bookings.confirm', $this->booking))->assertSessionHas('success');
            $this->assertSame(BookingStatus::Confirmed, $this->booking->refresh()->status);
            $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('success');
            $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
        }
    }

    public function test_owner_and_guest_deadline_includes_midnight_but_not_one_second_later(): void
    {
        foreach ([false, true] as $guest) {
            $this->booking->update(['status' => BookingStatus::Pending]);
            RoomInventory::query()->update(['available' => 0]);
            if ($guest) $this->guestBooking();
            $url = $guest ? $this->guestUrl('cancel') : route('bookings.cancel', $this->booking);

            $this->travelTo(Carbon::parse('2026-09-17 00:00:01'));
            $this->post($url)->assertSessionHas('error');
            $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);

            $this->travelTo(Carbon::parse('2026-09-17 00:00:00'));
            $this->post($url)->assertSessionHas('success');
            $this->assertSame(BookingStatus::Cancelled, $this->booking->refresh()->status);
        }
    }

    public function test_guest_signatures_are_required_and_bound_to_route_and_booking(): void
    {
        $this->guestBooking();
        $url = $this->guestUrl('show');
        $this->get($url)->assertOk()->assertSee('Cancel booking');
        $this->get(route('guest.bookings.show', $this->booking))->assertForbidden();
        $this->get($url . '&changed=1')->assertForbidden();
        $this->post(route('guest.bookings.cancel', $this->booking))->assertForbidden();
        $this->post(str_replace('/manage?', '/cancel?', $url))->assertForbidden();
        $expired = URL::temporarySignedRoute('guest.bookings.show', now()->subSecond(), $this->booking);
        $this->get($expired)->assertForbidden();
        $this->post($this->guestUrl('cancel'))->assertSessionHas('success');
        $this->post($this->guestUrl('cancel'))->assertSessionHas('error');
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_guest_link_cannot_manage_account_owned_booking(): void
    {
        auth()->forgetUser();
        $this->get($this->guestUrl('show'))->assertForbidden();
        $this->post($this->guestUrl('cancel'))->assertForbidden();
    }

    public function test_guest_post_also_requires_csrf_protection(): void
    {
        $this->guestBooking();
        $url = $this->guestUrl('cancel');
        // Laravel normally bypasses CSRF middleware in the testing environment.
        $this->app['env'] = 'production';
        $this->post($url)->assertStatus(419);
        $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
    }

    public function test_signature_for_one_guest_booking_cannot_access_another(): void
    {
        $this->guestBooking();
        $other = $this->booking->replicate();
        $other->booking_number = 'BYH-OTHER';
        $other->save();
        $url = str_replace($this->booking->booking_number, $other->booking_number, $this->guestUrl('show'));
        $this->get($url)->assertForbidden();
    }

    public function test_terminal_statuses_cannot_change_even_for_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([BookingStatus::Cancelled, BookingStatus::Completed, BookingStatus::Rejected, BookingStatus::Expired] as $status) {
            $this->booking->update(['status' => $status]);
            foreach (['cancel', 'confirm', 'complete'] as $action) {
                $this->post(route('bookings.' . $action, $this->booking))->assertSessionHas('error');
                $this->assertSame($status, $this->booking->refresh()->status);
            }
        }
        $this->assertSame([0, 0], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_completion_requires_confirmation_and_check_out_and_never_restores_inventory(): void
    {
        $this->actingAs($this->supplier);
        $this->post(route('bookings.complete', $this->booking))->assertSessionHas('error');
        $this->post(route('bookings.confirm', $this->booking))->assertSessionHas('success');
        $this->post(route('bookings.complete', $this->booking))->assertSessionHas('error');
        $this->travelTo(Carbon::parse('2026-09-20 00:00:00'));
        $this->post(route('bookings.complete', $this->booking))->assertSessionHas('success');
        $this->assertSame(BookingStatus::Completed, $this->booking->refresh()->status);
        $this->assertSame([0, 0], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_missing_inventory_rolls_back_cancellation_without_recreating_rows(): void
    {
        RoomInventory::where('date', '2026-09-19')->delete();
        $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('error');
        $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
        $this->assertDatabaseCount('room_inventories', 1);
        $this->assertSame(0, RoomInventory::firstOrFail()->available);
        $this->assertDatabaseCount('booking_items', 2);
    }

    public function test_booking_cannot_be_deleted_through_model_or_http(): void
    {
        $this->delete(route('bookings.show', $this->booking))->assertStatus(405);
        $this->expectException(BookingException::class);
        $this->booking->delete();
    }

    public function test_failure_saving_cancelled_status_rolls_back_inventory_changes(): void
    {
        Booking::updating(function (Booking $booking) {
            if ($booking->status === BookingStatus::Cancelled) {
                throw new BookingException('Simulated status write failure.');
            }
        });

        $this->post(route('bookings.cancel', $this->booking))->assertSessionHas('error');
        $this->assertSame(BookingStatus::Pending, $this->booking->refresh()->status);
        $this->assertSame([0, 0], RoomInventory::orderBy('date')->pluck('available')->all());
        $this->assertDatabaseCount('booking_items', 2);
    }
}
