<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Services\HotelSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_catalog_cleanup_archives_duplicate_boards_and_remaps_demo_pivots_without_changing_snapshots(): void
    {
        $supplier = DB::table('users')->insertGetId(['name' => 'Supplier', 'email' => 'supplier@example.test', 'password' => 'x', 'role' => 'supplier']);
        $hotel = Hotel::create(['supplier_id' => $supplier, 'name' => 'Hotel', 'address' => '1 Test Street', 'city' => 'Paris', 'country' => 'France']);
        $room = Room::create(['hotel_id' => $hotel->id, 'room_type_id' => DB::table('room_types')->insertGetId(['name' => 'Suite']), 'bed_type_id' => DB::table('bed_types')->insertGetId(['name' => 'King']), 'name' => 'Suite', 'capacity' => 2, 'total_units' => 1, 'price_per_night' => 100]);
        $canonical = DB::table('board_types')->insertGetId(['code' => 'HB', 'name' => 'Half Board']);
        $duplicate = DB::table('board_types')->insertGetId(['code' => 'DEMO-2', 'name' => 'Half board']);
        DB::table('room_board_types')->insert(['room_id' => $room->id, 'board_type_id' => $duplicate, 'price' => 20, 'created_at' => now(), 'updated_at' => now()]);
        $booking = Booking::create(['hotel_id' => $hotel->id, 'booking_number' => 'CATALOG-1', 'guest_name' => 'Guest', 'guest_email' => 'guest@example.test', 'check_in' => now()->addDays(3), 'check_out' => now()->addDays(5), 'status' => 'completed', 'subtotal' => 200, 'total' => 200, 'currency' => 'EUR']);
        DB::table('booking_items')->insert(['booking_id' => $booking->id, 'room_id' => $room->id, 'board_type_id' => $duplicate, 'room_name' => 'Suite snapshot', 'board_name' => 'Half board snapshot', 'quantity' => 1, 'adults' => 2, 'children' => 0, 'price_per_night' => 100, 'subtotal' => 200, 'nights' => 2, 'check_in' => $booking->check_in, 'check_out' => $booking->check_out, 'currency' => 'EUR', 'created_at' => now(), 'updated_at' => now()]);

        $this->artisan('demo:catalog', ['--apply' => true])->assertSuccessful();

        $this->assertDatabaseHas('board_types', ['id' => $duplicate]);
        $this->assertNotNull(DB::table('board_types')->where('id', $duplicate)->value('archived_at'));
        $this->assertDatabaseHas('room_board_types', ['room_id' => $room->id, 'board_type_id' => $canonical]);
        $this->assertDatabaseHas('booking_items', ['booking_id' => $booking->id, 'board_type_id' => $duplicate, 'room_name' => 'Suite snapshot', 'board_name' => 'Half board snapshot']);
    }

    public function test_duplicate_facility_is_remapped_and_public_options_are_semantically_unique(): void
    {
        $supplier = DB::table('users')->insertGetId(['name' => 'Supplier', 'email' => 'supplier2@example.test', 'password' => 'x', 'role' => 'supplier']);
        $hotel = Hotel::create(['supplier_id' => $supplier, 'name' => 'Visible', 'address' => '1 Test Street', 'city' => 'Paris', 'country' => 'France', 'published' => true, 'facilities' => ['Wi-Fi', 'wifi', 'Pool', 'pool']]);
        $room = Room::create(['hotel_id' => $hotel->id, 'room_type_id' => DB::table('room_types')->insertGetId(['name' => 'Double']), 'bed_type_id' => DB::table('bed_types')->insertGetId(['name' => 'King']), 'name' => 'Double', 'capacity' => 2, 'total_units' => 1, 'price_per_night' => 100]);
        DB::table('board_types')->insert([['code' => 'HB', 'name' => 'Half Board'], ['code' => 'DEMO-2', 'name' => 'Half board']]);
        $canonical = DB::table('facilities')->insertGetId(['name' => 'Balcony']);
        $duplicate = DB::table('facilities')->insertGetId(['name' => 'Demo Balcony']);
        DB::table('facility_room')->insert(['room_id' => $room->id, 'facility_id' => $duplicate]);

        $this->artisan('demo:catalog', ['--apply' => true])->assertSuccessful();

        $this->assertDatabaseMissing('facilities', ['id' => $duplicate]);
        $this->assertDatabaseHas('facility_room', ['room_id' => $room->id, 'facility_id' => $canonical]);
        $options = app(HotelSearchService::class)->options();
        $this->assertSame($options['hotelFacilities']->count(), $options['hotelFacilities']->pluck('key')->unique()->count());
        $this->assertSame($options['roomFacilities']->count(), $options['roomFacilities']->pluck('semantic_key')->unique()->count());
        $this->assertSame($options['boards']->count(), $options['boards']->pluck('semantic_key')->unique()->count());
        $response = $this->actingAs(\App\Models\User::findOrFail($supplier))->get(route('supplier.hotels.rooms.create', $hotel));
        $this->assertSame(1, substr_count($response->getContent(), 'Half Board'));
        $this->assertSame(1, substr_count($response->getContent(), 'Balcony'));
    }
}
