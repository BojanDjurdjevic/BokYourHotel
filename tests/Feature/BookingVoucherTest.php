<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\Support\CreatesBookingScenario;
use Tests\TestCase;
class BookingVoucherTest extends TestCase
{
    use RefreshDatabase, CreatesBookingScenario;
    protected function setUp(): void { parent::setUp(); $this->scenario(); }
    public function test_owner_voucher_has_all_items_and_server_totals(): void {
        $second=$this->room->replicate(); $second->name='Second room'; $second->save();
        $board=\Illuminate\Support\Facades\DB::table('board_types')->insertGetId(['name'=>'Second board','code'=>'HB']);
        $second->boardTypes()->attach($board,['price'=>10]);
        $data=$this->payload(); $data['items'][]=['room_id'=>$second->id,'board_type_id'=>$board,'quantity'=>1,'adults'=>1,'children'=>1];
        $this->actingAs($this->owner);
        $booking=app(\App\Services\BookingService::class)->create($data);
        $this->get(route('bookings.voucher',['booking'=>$booking,'total'=>1,'guest_name'=>'Spoof']))->assertOk()
            ->assertSee($booking->booking_number)->assertSee('Second room')->assertSee('Second board')->assertSee('440.00')->assertDontSee('Spoof')
            ->assertHeader('Cache-Control','no-store, private')->assertHeader('Content-Type','text/html; charset=utf-8');
    }
    public function test_other_user_and_foreign_supplier_are_forbidden_staff_uses_policy(): void {
        $booking=$this->reservation();
        foreach ([User::factory()->create(),User::factory()->create(['role'=>'supplier'])] as $user) {
            $this->actingAs($user)->get(route('bookings.voucher',$booking))->assertForbidden();
        }
        foreach ([$this->supplier,User::factory()->create(['role'=>'admin']),User::factory()->create(['role'=>'superadmin'])] as $user) {
            $this->actingAs($user)->get(route('bookings.voucher',$booking))->assertOk();
        }
    }
    public function test_guest_requires_valid_untampered_signature_and_guest_booking(): void {
        $guest=$this->reservation(true);
        $this->get(route('guest.bookings.voucher',$guest))->assertForbidden();
        $url=URL::temporarySignedRoute('guest.bookings.voucher',now()->addHour(),$guest);
        $this->get($url)->assertOk()->assertSee('Guest Test');
        $this->get($url.'&total=1')->assertForbidden();
        $this->get(URL::temporarySignedRoute('guest.bookings.voucher',now()->subMinute(),$guest))->assertForbidden();
        $owned=$this->reservation(); auth()->logout();
        $this->get(URL::temporarySignedRoute('guest.bookings.voucher',now()->addHour(),$owned))->assertForbidden();
    }
    public function test_cancelled_voucher_is_clearly_not_valid_for_stay(): void {
        $booking=$this->reservation();
        app(\App\Services\FakePaymentService::class)->submit($booking,$this->owner,1,'success');
        app(\App\Services\BookingService::class)->cancel($booking,$this->owner,'Changed plans');
        $this->get(route('bookings.voucher',$booking))->assertOk()->assertSee('CANCELLED')->assertSee('Refund recorded in the local payment simulation');
    }
}
