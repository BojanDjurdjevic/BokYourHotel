<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! app()->environment(['local','testing'])) throw new RuntimeException('Local/testing only.');
$request = Illuminate\Http\Request::create('/');
$request->setLaravelSession(app('session')->driver());
app()->instance('request', $request);
Illuminate\Support\Facades\View::share('errors', new Illuminate\Support\ViewErrorBag());
$supplier = App\Models\User::where('email','supplier01@demo.bookyourhotel.test')->first() ?? App\Models\User::where('role','supplier')->first();
$hotel = App\Models\Hotel::where('published',1)->first();
$ownedHotel = $supplier?->hotels()->first();
$ownedRoom = $ownedHotel?->rooms()->first();
$customer = App\Models\User::where('email', 'user@demo.bookyourhotel.test')->first();
$admin = App\Models\User::where('email', 'admin@demo.bookyourhotel.test')->first();
$customerBooking = $customer ? App\Models\Booking::where('user_id', $customer->id)->first() : null;
$supplierBooking = $ownedHotel ? App\Models\Booking::where('hotel_id', $ownedHotel->id)->first() : null;
$searchRequest = App\Http\Requests\HotelSearchRequest::createFrom($request);
$searchRequest->setContainer(app())->setRedirector(app('redirect'));
$searchRequest->validateResolved();
$cases = [
 'public_listing'=>fn()=>app(App\Http\Controllers\PublicHotelController::class)->index($searchRequest, app(App\Services\HotelSearchService::class)),
 'supplier_hotels'=>fn()=>app(App\Http\Controllers\Supplier\HotelController::class)->index(),
 'supplier_dashboard'=>fn()=>app(App\Http\Controllers\SupplierController::class)->index(),
 'bookings_index'=>fn()=>app(App\Http\Controllers\Booking\BookingManagementController::class)->index($request),
];
if($hotel){
 $cases['hotel_details']=fn()=>app(App\Http\Controllers\PublicHotelController::class)->show($hotel->fresh());
 $cases['availability']=fn()=>app(App\Services\AvailabilityService::class)->getAvailability($hotel->fresh(),now()->startOfDay()->addDays(3),now()->startOfDay()->addDays(6));
}
if ($ownedHotel && $ownedRoom) {
 $cases['supplier_rooms'] = fn()=>app(App\Http\Controllers\Supplier\RoomController::class)->index($ownedHotel->fresh());
 $cases['supplier_inventory'] = fn()=>app(App\Http\Controllers\Supplier\RoomInventoryController::class)->index($ownedHotel->fresh());
 $cases['supplier_pending'] = fn()=>app(App\Http\Controllers\SupplierController::class)->pending();
 $cases['supplier_confirmed'] = fn()=>app(App\Http\Controllers\SupplierController::class)->confirmed();
 $cases['demo_hotel_details'] = fn()=>app(App\Http\Controllers\PublicHotelController::class)->show($ownedHotel->fresh());
}
if ($supplierBooking) $cases['supplier_booking_details'] = fn()=>app(App\Http\Controllers\Booking\BookingManagementController::class)->show($supplierBooking->fresh());
if ($customerBooking) {
 $cases['user_bookings'] = fn()=>app(App\Http\Controllers\Booking\BookingManagementController::class)->index($request);
 $cases['user_booking_details'] = fn()=>app(App\Http\Controllers\Booking\BookingManagementController::class)->show($customerBooking->fresh());
 $cases['user_payment'] = fn()=>app(App\Http\Controllers\Booking\PaymentController::class)->show($request, $customerBooking->fresh());
}
if ($admin) $cases['admin_bookings'] = fn()=>app(App\Http\Controllers\Booking\BookingManagementController::class)->index($request);
if ($supplier) { auth()->setUser($supplier); $request->setUserResolver(fn()=>$supplier); }
$result=[];
foreach($cases as $name=>$call){
 if(!$supplier && str_starts_with($name,'supplier'))continue;
 $actor = str_starts_with($name, 'user_') ? $customer : ($name === 'admin_bookings' ? $admin : $supplier);
 if ($actor) { auth()->setUser($actor); $request->setUserResolver(fn()=>$actor); }
 Illuminate\Support\Facades\DB::flushQueryLog();
 Illuminate\Support\Facades\DB::enableQueryLog();
 $start=microtime(true);
 try { $response=$call(); if($response instanceof Illuminate\Contracts\View\View)$response->render();
 $result[$name]=['queries'=>count(Illuminate\Support\Facades\DB::getQueryLog()),'milliseconds'=>round((microtime(true)-$start)*1000,2)];
 }catch(Throwable $e){$result[$name]=['error'=>$e->getMessage()];}
 Illuminate\Support\Facades\DB::disableQueryLog();
}
echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
