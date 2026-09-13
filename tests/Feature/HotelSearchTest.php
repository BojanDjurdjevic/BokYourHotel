<?php
namespace Tests\Feature;
use App\Models\{Hotel,RoomInventory};
use App\Services\{HotelSearchService,AvailabilityService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesBookingScenario;
use Tests\TestCase;
class HotelSearchTest extends TestCase
{
    use RefreshDatabase, CreatesBookingScenario;
    protected function setUp(): void { parent::setUp(); $this->scenario(); }
    private function search(array $data = []) { return $this->get(route('hotels.index', $data)); }
    public function test_search_results_anchor_scrolls_only_after_a_query_request(): void {
        $this->search()->assertOk()->assertSee('id="hotel-results"', false)->assertDontSee('scrollIntoView');
        $this->search(['city' => 'Paris'])->assertOk()->assertSee('id="hotel-results"', false)->assertSee('scrollIntoView');
    }
    public function test_autocomplete_is_distinct_published_limited_and_literal(): void {
        $this->hotel->replicate()->save();
        $other = $this->hotel->replicate(); $other->city = 'Palermo'; $other->published = false; $other->save();
        $this->getJson(route('destinations.index',['q'=>'pa']))->assertOk()->assertExactJson([['city'=>'Paris','country'=>'France']]);
        $this->getJson(route('destinations.index',['q'=>'p']))->assertUnprocessable();
        $this->getJson(route('destinations.index',['q'=>'%%']))->assertExactJson([]);
        for ($i=0;$i<10;$i++) { $h=$this->hotel->replicate(); $h->city='Paris '.$i; $h->save(); }
        $this->getJson(route('destinations.index',['q'=>'pa']))->assertJsonCount(8);
    }
    public function test_all_room_filters_apply_to_one_room_and_hotel_facilities_are_separate(): void {
        $air = DB::table('facilities')->insertGetId(['name'=>'Air conditioning']);
        $balcony = DB::table('facilities')->insertGetId(['name'=>'Balcony']);
        $this->room->facilities()->attach($air);
        $second = $this->room->replicate(); $second->save(); $second->facilities()->attach($balcony); $second->boardTypes()->attach($this->board,['price'=>10]);
        $filter=['city'=>'Paris','country'=>'France','adults'=>2,'stars'=>[5],'hotel_facilities'=>['Pool'],'room_facilities'=>[$air,$balcony],'board_type'=>$this->board];
        $this->search($filter)->assertOk()->assertDontSee('Test Palace');
        $this->room->facilities()->attach($balcony);
        $this->search($filter)->assertOk()->assertSee('Test Palace');
        $this->search(array_replace($filter,['hotel_facilities'=>['Air conditioning']]))->assertDontSee('Test Palace');
        $this->search(array_replace($filter,['children'=>1]))->assertDontSee('Test Palace');
        $this->search(array_replace($filter,['country'=>'USA']))->assertDontSee('Test Palace');
        $this->search(array_replace($filter,['stars'=>[4]]))->assertDontSee('Test Palace');
    }
    public function test_whole_period_availability_and_prices_match_existing_service(): void {
        RoomInventory::create(['room_id'=>$this->room->id,'date'=>'2026-10-05','available'=>2,'price'=>150]);
        $dates=['check_in'=>'2026-10-05','check_out'=>'2026-10-07','adults'=>2,'board_type'=>$this->board];
        $response=$this->search($dates)->assertOk();
        $this->assertEquals(270, $response->viewData('hotels')->first()->search_price);
        $availability=app(AvailabilityService::class)->getAvailability($this->hotel,\Carbon\Carbon::parse($dates['check_in']),\Carbon\Carbon::parse($dates['check_out']));
        $this->assertEquals(270, $availability['rooms'][0]['room_total']+$availability['rooms'][0]['board_types'][0]['total']);
        // Sold out on check-out is irrelevant.
        RoomInventory::create(['room_id'=>$this->room->id,'date'=>'2026-10-07','available'=>0,'price'=>1]);
        $this->search($dates)->assertSee('Test Palace');
        $last = RoomInventory::create(['room_id'=>$this->room->id,'date'=>'2026-10-06','available'=>1,'price'=>null]);
        $this->assertEquals(270, $this->search($dates)->viewData('hotels')->first()->search_price);
        $last->update(['available'=>0]);
        $this->search($dates)->assertDontSee('Test Palace');
    }
    public function test_missing_dates_with_zero_default_units_are_unavailable(): void {
        $this->room->update(['total_units'=>0]);
        $dates=['check_in'=>'2026-10-05','check_out'=>'2026-10-07'];
        RoomInventory::create(['room_id'=>$this->room->id,'date'=>'2026-10-05','available'=>1,'price'=>100]);
        $this->search($dates)->assertDontSee('Test Palace');
        RoomInventory::create(['room_id'=>$this->room->id,'date'=>'2026-10-06','available'=>1,'price'=>100]);
        $this->search($dates)->assertSee('Test Palace');
    }
    public function test_price_sort_pagination_state_and_no_n_plus_one(): void {
        for ($i=0;$i<14;$i++) {
            $hotel=$this->hotel->replicate(); $hotel->name='Hotel '.$i; $hotel->star_rating=4; $hotel->save();
            $room=$this->room->replicate(); $room->hotel_id=$hotel->id; $room->price_per_night=200+$i; $room->save(); $room->boardTypes()->attach($this->board,['price'=>10]);
        }
        DB::enableQueryLog(); DB::flushQueryLog();
        $response=$this->search(['city'=>'Paris','country'=>'France','sort'=>'price_desc','adults'=>2])->assertOk();
        $this->assertLessThanOrEqual(8,count(DB::getQueryLog())); DB::disableQueryLog();
        $hotels=$response->viewData('hotels');
        $this->assertCount(12,$hotels); $this->assertEquals(223,$hotels->first()->search_price);
        $this->assertStringContainsString('country=France',$hotels->nextPageUrl());
        $this->assertStringContainsString('sort=price_desc',$hotels->nextPageUrl());
        $this->assertEquals(110,$this->search(['sort'=>'price_asc'])->viewData('hotels')->first()->search_price);
        $this->assertEquals(5,$this->search(['sort'=>'stars'])->viewData('hotels')->first()->star_rating);
        $this->assertSame(1,$this->search(['max_price'=>120])->viewData('hotels')->total());
        $this->assertSame(0,$this->search(['min_price'=>999])->viewData('hotels')->total());
    }
    public function test_invalid_search_and_booking_prefill(): void {
        $this->getJson('/?city[]=invalid')->assertUnprocessable();
        $this->getJson(route('hotels.index',['check_in'=>'2026-10-05']))->assertUnprocessable();
        $this->getJson(route('hotels.index',['check_in'=>'2026-10-05','check_out'=>'2027-10-05']))->assertUnprocessable();
        $this->getJson(route('hotels.index',['sort'=>'sql-injection']))->assertUnprocessable();
        $this->get(route('booking.show',['hotel'=>$this->hotel,'check_in'=>'2026-10-05','check_out'=>'2026-10-07','adults'=>2]))->assertOk()->assertViewHas('prefill',fn($p)=>$p['adults']==2);
    }
}
