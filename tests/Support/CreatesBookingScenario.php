<?php
namespace Tests\Support;
use App\Models\{User,Hotel,Room,Booking};
use Illuminate\Support\Facades\DB;
trait CreatesBookingScenario
{
    protected User $owner;
    protected User $supplier;
    protected Hotel $hotel;
    protected Room $room;
    protected int $board;
    protected function scenario(): void {
        $this->withoutVite();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-01 12:00:00'));
        config(['payments.fake_enabled' => true, 'mail.default' => 'array']);
        $this->owner = User::factory()->create();
        $this->supplier = User::factory()->create(['role'=>'supplier']);
        $this->hotel = Hotel::create(['supplier_id'=>$this->supplier->id,'name'=>'Test Palace','city'=>'Paris','country'=>'France','address'=>'Fictional Street','facilities'=>['Pool'],'star_rating'=>5]);
        $this->hotel->forceFill(['published'=>true])->save();
        $this->room = Room::create(['hotel_id'=>$this->hotel->id,'name'=>'King suite','capacity'=>2,'total_units'=>4,'price_per_night'=>100,
            'room_type_id'=>DB::table('room_types')->insertGetId(['name'=>'King']),
            'bed_type_id'=>DB::table('bed_types')->insertGetId(['name'=>'King'])]);
        $this->board = DB::table('board_types')->insertGetId(['name'=>'Breakfast','code'=>'BB']);
        $this->room->boardTypes()->attach($this->board,['price'=>10]);
    }
    protected function payload(): array {
        return ['hotel_id'=>$this->hotel->id,'check_in'=>'2026-10-05','check_out'=>'2026-10-07','guest_name'=>'Guest Test','guest_email'=>'guest@example.test',
            'items'=>[['room_id'=>$this->room->id,'board_type_id'=>$this->board,'quantity'=>1,'adults'=>2,'children'=>0]]];
    }
    protected function reservation(bool $guest = false): Booking {
        if ($guest) auth()->logout(); else $this->actingAs($this->owner);
        return app(\App\Services\BookingService::class)->create($this->payload());
    }
}
