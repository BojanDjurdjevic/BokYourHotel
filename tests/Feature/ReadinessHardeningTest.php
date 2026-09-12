<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomInventory;
use App\Models\User;
use App\Services\BookingService;
use App\Services\FakePaymentService;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReadinessHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $supplier;
    private User $owner;
    private Hotel $hotel;
    private Room $room;
    private int $board;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->travelTo(Carbon::parse('2026-10-01 12:00:00'));
        config(['payments.fake_enabled' => true]);
        $this->supplier = User::factory()->create(['role' => 'supplier']);
        $this->owner = User::factory()->create();
        $this->hotel = Hotel::create(['supplier_id' => $this->supplier->id, 'name' => 'Test Hotel', 'city' => 'Paris', 'country' => 'France', 'address' => 'Test Street']);
        $this->hotel->forceFill(['published' => true])->save();
        $this->room = Room::create([
            'hotel_id' => $this->hotel->id, 'name' => 'Double', 'capacity' => 2, 'total_units' => 3, 'price_per_night' => 100,
            'room_type_id' => DB::table('room_types')->insertGetId(['name' => 'Double']),
            'bed_type_id' => DB::table('bed_types')->insertGetId(['name' => 'King']),
        ]);
        $this->board = DB::table('board_types')->insertGetId(['name' => 'Breakfast', 'code' => 'BB']);
        $this->room->boardTypes()->attach($this->board, ['price' => 10]);
        $this->actingAs($this->owner);
    }

    private function booking(): Booking
    {
        return app(BookingService::class)->create([
            'hotel_id' => $this->hotel->id, 'check_in' => '2026-10-05', 'check_out' => '2026-10-07',
            'guest_name' => 'Guest', 'guest_email' => 'guest@example.test',
            'items' => [['room_id' => $this->room->id, 'board_type_id' => $this->board, 'quantity' => 1, 'adults' => 2]],
        ]);
    }

    public function test_hold_expires_once_and_rejects_late_payment_and_confirmation(): void
    {
        $booking = $this->booking();
        $this->assertSame('2026-10-01 12:30:00', $booking->locked_until->toDateTimeString());
        $this->get(route('payments.show', $booking))->assertSee('12:30:00');
        $this->travel(30)->minutes();
        $this->post(route('payments.submit', $booking), ['attempt' => 1, 'outcome' => 'success'])->assertSessionHas('error');
        $this->actingAs($this->supplier)->post(route('bookings.confirm', $booking))->assertSessionHas('error');
        $this->artisan('bookings:expire')->assertSuccessful();
        $this->artisan('bookings:expire')->assertSuccessful();
        $this->assertSame(BookingStatus::Expired, $booking->refresh()->status);
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('available')->all());
        $this->assertSame([3, 3], RoomInventory::orderBy('date')->pluck('version')->all());
        $this->assertDatabaseCount('booking_items', 1);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_payment_before_deadline_prevents_expiry(): void
    {
        $booking = $this->booking();
        $this->travel(29)->minutes();
        app(FakePaymentService::class)->submit($booking, $this->owner, 1, 'success');
        $this->travel(2)->minutes();
        $this->assertFalse(app(BookingService::class)->expire($booking));
        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
        $this->assertNull($booking->locked_until);
        $this->assertSame(PaymentStatus::Paid, $booking->payment->status);
        $this->assertSame([2, 2], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_failed_payment_expires_but_confirmed_unpaid_does_not(): void
    {
        $failed = $this->booking();
        app(FakePaymentService::class)->submit($failed, $this->owner, 1, 'failure');
        $confirmed = $this->booking();
        app(BookingService::class)->confirm($confirmed, $this->supplier);
        $this->travel(31)->minutes();
        $this->artisan('bookings:expire')->assertSuccessful();
        $this->assertSame(BookingStatus::Expired, $failed->refresh()->status);
        $this->assertSame(PaymentStatus::Failed, $failed->payment->status);
        $this->assertSame(BookingStatus::Confirmed, $confirmed->refresh()->status);
        $this->assertSame([2, 2], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_expiry_rolls_back_if_inventory_is_missing(): void
    {
        $booking = $this->booking();
        RoomInventory::whereDate('date', '2026-10-06')->delete();
        $this->travel(31)->minutes();
        $this->artisan('bookings:expire')->assertFailed();
        $this->assertSame(BookingStatus::Pending, $booking->refresh()->status);
        $this->assertSame([2], RoomInventory::pluck('available')->all());
    }

    public function test_missing_inventory_snapshot_cannot_overwrite_a_new_booking(): void
    {
        $snapshot = app(InventoryService::class)->snapshot($this->room, $this->supplier, '2026-10-05', '2026-10-06');
        $this->assertSame(0, $snapshot[0]['version']);
        $this->booking();
        $this->actingAs($this->supplier)->putJson(route('supplier.rooms.inventory.update', $this->room), $snapshot[0])->assertStatus(409);
        $this->assertSame([2, 2], RoomInventory::orderBy('date')->pluck('available')->all());
    }

    public function test_bulk_stale_write_is_atomic_and_current_edit_increments_version(): void
    {
        $this->booking();
        $inventory = app(InventoryService::class);
        $snapshot = $inventory->snapshot($this->room, $this->supplier, '2026-10-05', '2026-10-06');
        $inventory->update($this->room, $this->supplier, [array_replace($snapshot[1], ['price' => 200])]);
        $rows = array_map(fn ($row) => array_replace($row, ['available' => 3]), $snapshot);
        $this->actingAs($this->supplier)->putJson(route('supplier.rooms.inventory.bulk', $this->room), ['rows' => $rows])->assertStatus(409);
        $this->assertSame([2, 2], RoomInventory::orderBy('date')->pluck('available')->all());
        $this->assertSame([2, 3], RoomInventory::orderBy('date')->pluck('version')->all());
        $this->getJson(route('supplier.rooms.inventory.preview', ['room' => $this->room, 'from' => '2026-10-01', 'to' => '2030-01-01']))->assertUnprocessable();
    }

    public function test_setup_cannot_override_existing_inventory_even_with_supplied_version(): void
    {
        $this->booking();
        $this->actingAs($this->supplier)->postJson(route('supplier.hotels.inventory.store', $this->hotel), [
            'room_id' => $this->room->id, 'inventory_json' => json_encode([['date' => '2026-10-05', 'available' => 3, 'price' => 100, 'version' => 2]]),
        ])->assertStatus(409);
        $this->assertSame(2, RoomInventory::whereDate('date', '2026-10-05')->value('available'));
    }

    public function test_supplier_hotel_pagination_has_constant_query_count(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $hotel = $this->hotel->replicate();
            $hotel->name = 'Hotel '.$i;
            $hotel->save();
        }
        $this->actingAs($this->supplier);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->get(route('supplier.hotels.index'))->assertOk();
        $selects = collect(DB::getQueryLog())->filter(fn ($query) => str_starts_with(strtolower($query['query']), 'select'));
        $this->assertLessThanOrEqual(4, $selects->count());
        $this->assertCount(12, $response->viewData('hotels'));
        $this->assertSame(16, $response->viewData('hotels')->total());
        DB::disableQueryLog();
    }

    public function test_public_creation_rate_limit_is_enforced(): void
    {
        for ($i = 0; $i < 10; $i++) $this->postJson(route('booking.store'), [])->assertUnprocessable();
        $this->postJson(route('booking.store'), [])->assertStatus(429);
    }

    public function test_supplier_room_list_is_paginated_without_image_n_plus_one(): void
    {
        for ($i = 0; $i < 15; $i++) $this->room->replicate()->save();
        $this->actingAs($this->supplier);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $response = $this->get(route('supplier.hotels.rooms.index', $this->hotel))->assertOk();
        // One additional bounded query supplies the authenticated navigation unread count.
        $this->assertLessThanOrEqual(8, collect(DB::getQueryLog())->filter(fn ($q) => str_starts_with(strtolower($q['query']), 'select'))->count());
        $this->assertCount(12, $response->viewData('rooms'));
        $this->assertSame(16, $response->viewData('rooms')->total());
        DB::disableQueryLog();
    }

    public function test_demo_image_import_requires_local_approved_assets_and_is_repeatable(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $root = storage_path('framework/testing/demo-assets-'.bin2hex(random_bytes(8)));
        \Illuminate\Support\Facades\File::makeDirectory($root.'/demo/images', 0755, true);
        $file = \Illuminate\Http\UploadedFile::fake()->image('source.jpg');
        copy($file->getRealPath(), $root.'/demo/images/allowed.jpg');
        copy($file->getRealPath(), $root.'/demo/outside.jpg');
        file_put_contents($root.'/demo/manifest.json', json_encode([
            'categories' => ['exterior'],
            'assets' => [
                ['filename' => 'allowed.jpg', 'category' => 'exterior', 'approved' => true, 'license_note' => 'Generated test fixture'],
                ['filename' => '../outside.jpg', 'category' => 'exterior', 'approved' => true, 'license_note' => 'Test traversal'],
                ['filename' => 'missing.jpg', 'category' => 'exterior', 'approved' => true, 'license_note' => 'Missing'],
                ['filename' => 'allowed.jpg', 'category' => 'exterior', 'approved' => false, 'license_note' => 'Not approved'],
            ],
        ]));
        DB::table('demo_seed_runs')->insert(['name' => 'portfolio-v1', 'anchor_date' => now()->toDateString(), 'summary' => json_encode(['hotel_ids' => [$this->hotel->id]])]);
        try {
            config(['demo.asset_directory' => $root.'/demo']);
            $this->artisan('demo:images')->assertSuccessful();
            $this->artisan('demo:images')->assertSuccessful();
            $this->assertDatabaseCount('hotel_images', 1);
            $image = $this->hotel->images()->sole();
            $this->assertStringStartsWith('hotels/'.$this->hotel->id.'/demo-', $image->path);
            \Illuminate\Support\Facades\Storage::disk('public')->assertExists($image->path);
        } finally {
            config(['demo.asset_directory' => resource_path('demo')]);
            // Only files created by this test, beneath its random workspace testing directory.
            unlink($root.'/demo/images/allowed.jpg');
            unlink($root.'/demo/outside.jpg');
            unlink($root.'/demo/manifest.json');
            rmdir($root.'/demo/images');
            rmdir($root.'/demo');
            rmdir($root);
        }
    }

    public function test_room_upload_rejects_unsafe_types_and_normalizes_valid_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->actingAs($this->supplier);
        foreach ([
            \Illuminate\Http\UploadedFile::fake()->createWithContent('payload.jpg', '<?php echo "unsafe";'),
            \Illuminate\Http\UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
            \Illuminate\Http\UploadedFile::fake()->image('large.jpg')->size(4097),
        ] as $file) {
            $this->postJson(route('supplier.rooms.images.store', $this->room), ['images' => [$file]])->assertUnprocessable();
        }
        $this->assertDatabaseCount('room_images', 0);
        $this->post(route('supplier.rooms.images.store', $this->room), [
            'images' => [\Illuminate\Http\UploadedFile::fake()->image('room.jpg', 1600, 900)],
        ])->assertSessionHas('success');
        $image = $this->room->images()->sole();
        $this->assertMatchesRegularExpression('#^rooms/'.$this->room->id.'/[a-f0-9-]+\.webp$#', $image->path);
        $dimensions = getimagesize(\Illuminate\Support\Facades\Storage::disk('public')->path($image->path));
        $this->assertSame(1200, $dimensions[0]);
        $this->assertSame('image/webp', $dimensions['mime']);
    }

    public function test_upload_failure_does_not_create_an_image_record(): void
    {
        $disk = \Mockery::mock(\Illuminate\Filesystem\FilesystemAdapter::class);
        $disk->shouldReceive('put')->once()->andReturn(false);
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')->with('public')->andReturn($disk);
        try {
            app(\App\Actions\Rooms\UploadRoomImage::class)->execute($this->room, \Illuminate\Http\UploadedFile::fake()->image('room.jpg'));
            $this->fail('Storage failure must propagate.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Image storage write failed.', $e->getMessage());
        }
        $this->assertDatabaseCount('room_images', 0);
    }

    public function test_supplier_cannot_mutate_foreign_inventory_or_upload_to_foreign_room(): void
    {
        $other = User::factory()->create(['role' => 'supplier']);
        $this->actingAs($other);
        $this->getJson(route('supplier.rooms.inventory.preview', ['room' => $this->room, 'from' => '2026-10-05', 'to' => '2026-10-06']))->assertForbidden();
        $this->post(route('supplier.rooms.images.store', $this->room), ['images' => [\Illuminate\Http\UploadedFile::fake()->image('room.jpg')]])->assertForbidden();
        $this->putJson(route('supplier.rooms.inventory.bulk', $this->room), ['rows' => []])->assertForbidden();
        $this->assertDatabaseCount('room_images', 0);
        $this->assertDatabaseCount('room_inventories', 0);
    }
}
