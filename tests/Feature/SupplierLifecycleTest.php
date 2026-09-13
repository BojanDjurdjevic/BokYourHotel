<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\BoardType;
use App\Models\User;
use App\Services\BookingService;
use App\Services\FakePaymentService;
use App\Services\SupplierLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\CreatesBookingScenario;
use Tests\TestCase;

class SupplierLifecycleTest extends TestCase
{
    use RefreshDatabase, CreatesBookingScenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scenario();
    }

    public function test_unused_hotel_is_archived_and_kept_in_supplier_list(): void
    {
        $this->actingAs($this->supplier)->delete(route('supplier.hotels.destroy', $this->hotel))->assertRedirect();
        $this->assertNotNull($this->hotel->refresh()->archived_at);
        $this->assertFalse((bool) $this->hotel->published);
        $this->assertNotNull($this->room->refresh()->archived_at);
        $this->get(route('supplier.hotels.index'))->assertOk()->assertSee('Test Palace')->assertSee('Archived');
        $this->get(route('supplier.hotels.setup.info', $this->hotel))->assertForbidden();
        $this->put(route('supplier.hotels.setup.publishHotel', $this->hotel))->assertForbidden();
    }

    public static function activeBookings(): array
    {
        return [['pending', false], ['confirmed', false], ['pending', true], ['confirmed', true]];
    }

    #[DataProvider('activeBookings')]
    public function test_unresolved_bookings_block_hotel_room_and_supplier_retirement(string $status, bool $paid): void
    {
        $booking = $this->reservation();
        if ($paid) app(FakePaymentService::class)->submit($booking, $this->owner, 1, 'success');
        $booking->update(['status' => $status]);
        $this->actingAs($this->supplier);
        $this->delete(route('supplier.hotels.destroy', $this->hotel))->assertSessionHasErrors('lifecycle');
        $this->delete(route('supplier.hotels.rooms.destroy', [$this->hotel, $this->room]))->assertSessionHasErrors('lifecycle');
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasErrors('lifecycle', null, 'userDeletion');
        $this->assertAuthenticatedAs($this->supplier);
        $this->assertNull($this->hotel->refresh()->archived_at);
        $this->assertNull($this->room->refresh()->archived_at);
        $this->assertNull($this->supplier->refresh()->supplier_deactivated_at);
        $this->assertSame($status, $booking->refresh()->status->value);
    }

    public function test_completed_paid_history_and_snapshots_survive_room_and_hotel_archive(): void
    {
        $booking = $this->reservation();
        app(FakePaymentService::class)->submit($booking, $this->owner, 1, 'success');
        app(BookingService::class)->confirm($booking, $this->supplier);
        $this->travelTo(now()->addDays(10));
        app(BookingService::class)->complete($booking, $this->supplier);
        $payment = $booking->payment->getAttributes();
        $item = $booking->items->first()->getAttributes();
        $this->room->update(['name' => 'Renamed suite', 'capacity' => 9, 'price_per_night' => 999]);
        BoardType::findOrFail($this->board)->update(['name' => 'Renamed meal']);
        $this->actingAs($this->supplier)->delete(route('supplier.hotels.rooms.destroy', [$this->hotel, $this->room]))->assertSessionHasNoErrors();
        $this->get(route('supplier.hotels.rooms.index', $this->hotel))->assertOk()->assertSee('Archived');
        $this->get(route('supplier.hotels.rooms.edit', [$this->hotel, $this->room]))->assertForbidden();
        $this->get(route('supplier.rooms.images.index', $this->room))->assertForbidden();
        $this->get(route('supplier.rooms.inventory', $this->room))->assertForbidden();
        $this->delete(route('supplier.hotels.destroy', $this->hotel))->assertSessionHasNoErrors();
        $this->assertSame($item, $booking->items()->first()->getAttributes());
        $this->assertSame($payment, $booking->payment()->first()->getAttributes());
        $this->assertSame(BookingStatus::Completed, $booking->refresh()->status);
        $this->assertNotNull($booking->hotel);
        $this->assertNotNull($booking->items->first()->room);
        $this->get(route('bookings.show', $booking))->assertOk()->assertSee('Test Palace')->assertSee('King suite');
        $this->actingAs($this->owner)->get(route('bookings.voucher', $booking))->assertOk()
            ->assertSee('King suite')->assertSee('Breakfast')->assertSee('220.00')
            ->assertDontSee('Renamed suite')->assertDontSee('Renamed meal');
    }

    public function test_refunded_history_survives_supplier_deactivation_and_access_is_revoked(): void
    {
        $booking = $this->reservation();
        app(FakePaymentService::class)->submit($booking, $this->owner, 1, 'success');
        app(BookingService::class)->cancel($booking, $this->owner);
        $payment = $booking->payment()->first()->getAttributes();
        $this->actingAs($this->supplier)->delete(route('profile.destroy'), ['password' => 'password'])->assertRedirect('/')->assertSessionHasNoErrors();
        $this->assertGuest();
        $this->assertNotNull($this->supplier->refresh()->supplier_deactivated_at);
        $this->assertSame($this->supplier->id, $this->hotel->refresh()->supplier_id);
        $this->assertNotNull($this->hotel->archived_at);
        $this->assertSame($payment, $booking->payment()->first()->getAttributes());
        $this->post('/login', ['email' => $this->supplier->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->actingAs($this->supplier)->get(route('supplier.hotels.index'))->assertForbidden();
        $this->actingAs($this->owner)->get(route('bookings.voucher', $booking))->assertOk()->assertSee('Refund recorded in the local payment simulation')->assertSee('King suite');
    }

    public function test_archived_hotel_is_absent_from_public_surfaces_and_rejects_booking(): void
    {
        app(SupplierLifecycleService::class)->archiveHotel($this->hotel, $this->supplier);
        // Even a stale publication flag cannot expose an archived property.
        $this->hotel->refresh()->forceFill(['published' => true])->save();
        $this->getJson(route('destinations.index', ['q' => 'pa']))->assertExactJson([]);
        $this->get(route('hotels.index'))->assertOk()->assertDontSee('Test Palace');
        $this->get(route('hotels.show', $this->hotel))->assertNotFound();
        $this->get(route('booking.show', $this->hotel))->assertNotFound();
        $this->postJson(route('booking.store'), $this->payload())->assertUnprocessable();
        $this->assertDatabaseCount('bookings', 0);
        $this->expectException(\App\Exceptions\BookingException::class);
        app(BookingService::class)->create($this->payload());
    }

    public function test_archived_room_cannot_be_booked_or_appear_in_availability(): void
    {
        app(SupplierLifecycleService::class)->archiveRoom($this->room, $this->supplier);
        $this->getJson(route('booking.availability', [$this->hotel, 'check_in' => '2026-10-05', 'check_out' => '2026-10-07']))->assertOk()->assertJsonCount(0, 'rooms');
        $this->get(route('hotels.index', ['adults' => 2]))->assertDontSee('Test Palace');
        $this->postJson(route('booking.store'), $this->payload())->assertUnprocessable();
        $this->expectException(\App\Exceptions\BookingException::class);
        app(BookingService::class)->create($this->payload());
    }

    public function test_board_archive_keeps_history_and_removes_option_from_new_sales(): void
    {
        $booking = $this->reservation();
        $admin = User::factory()->create(['role' => 'admin']);
        app(SupplierLifecycleService::class)->archiveBoardType(BoardType::findOrFail($this->board), $admin);
        $this->assertCount(0, $this->room->boardTypes()->get());
        $this->assertNotNull($booking->items->first()->boardType);
        $this->get(route('bookings.voucher', $booking))->assertOk()->assertSee('Breakfast');
        $this->postJson(route('booking.store'), $this->payload())->assertUnprocessable();
    }

    public function test_foreign_supplier_cannot_archive_entities_and_superadmin_can_archive_but_not_edit_archive(): void
    {
        $other = User::factory()->create(['role' => 'supplier']);
        $this->actingAs($other)->delete(route('supplier.hotels.destroy', $this->hotel))->assertForbidden();
        $this->delete(route('supplier.hotels.rooms.destroy', [$this->hotel, $this->room]))->assertForbidden();
        $super = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($super)->delete(route('supplier.hotels.destroy', $this->hotel))->assertSessionHasNoErrors();
        $this->get(route('supplier.hotels.setup.info', $this->hotel))->assertForbidden();
    }

    public function test_database_restricts_direct_deletion_of_history_parents(): void
    {
        $booking = $this->reservation();
        foreach ([['users', $this->supplier->id], ['hotels', $this->hotel->id], ['rooms', $this->room->id], ['board_types', $this->board], ['bookings', $booking->id]] as [$table, $id]) {
            try {
                DB::table($table)->where('id', $id)->delete();
                $this->fail("Deletion of $table should be restricted.");
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertNotNull($e->errorInfo);
            }
        }
        $this->assertDatabaseCount('booking_items', 1);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_unused_supplier_is_deactivated_and_guest_deletion_keeps_booking(): void
    {
        $unused = User::factory()->create(['role' => 'supplier']);
        $this->actingAs($unused)->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasNoErrors();
        $this->assertNotNull($unused->refresh()->supplier_deactivated_at);
        $booking = $this->reservation();
        $this->delete(route('profile.destroy'), ['password' => 'password'])->assertSessionHasNoErrors();
        $this->assertNull($this->owner->fresh());
        $this->assertNull($booking->refresh()->user_id);
        $this->assertDatabaseCount('booking_items', 1);
    }

    public function test_database_allows_maintenance_deletion_of_unused_catalog_only(): void
    {
        DB::table('hotels')->where('id', $this->hotel->id)->delete();
        $this->assertDatabaseMissing('rooms', ['id' => $this->room->id]);
        $this->assertDatabaseCount('room_board_types', 0);
        $this->assertDatabaseHas('users', ['id' => $this->supplier->id]);
        DB::table('board_types')->where('id', $this->board)->delete();
        $this->assertDatabaseCount('board_types', 0);
    }
}
