<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment(['local','testing'])) throw new RuntimeException('Local/testing only.');
use Illuminate\Support\Facades\DB;
$service = app(App\Services\HotelSearchService::class);
$dates = ['check_in'=>now()->addDays(7)->toDateString(), 'check_out'=>now()->addDays(10)->toDateString(), 'adults'=>2, 'children'=>0];
$result = [];
foreach (['catalog'=>[], 'all_destinations_stay'=>$dates, 'paris_stay'=>$dates+['city'=>'Paris','country'=>'France','stars'=>[4,5],'hotel_facilities'=>['Pool'],'sort'=>'price_asc']] as $name=>$data) {
    $request=Illuminate\Http\Request::create('/hotels','GET',$data);
    $request->setLaravelSession(app('session')->driver()); app()->instance('request',$request);
    Illuminate\Support\Facades\View::share('errors',new Illuminate\Support\ViewErrorBag());
    DB::flushQueryLog(); DB::enableQueryLog();
    $hotels=$service->query($data)->paginate(12)->withQueryString();
    view('hotels.index',['hotels'=>$hotels]+$service->options())->render();
    $result[$name]=['queries'=>count(DB::getQueryLog()),'loaded_hotels'=>$hotels->count(),'total'=>$hotels->total()];
    DB::disableQueryLog();
    if (DB::getDriverName()==='mysql') $result[$name]['explain']=DB::select('EXPLAIN '.$service->query($data)->limit(12)->toSql(),$service->query($data)->limit(12)->getBindings());
}
DB::flushQueryLog(); DB::enableQueryLog();
app(App\Http\Controllers\DestinationController::class)(Illuminate\Http\Request::create('/destinations','GET',['q'=>'pa']));
$result['autocomplete']=['queries'=>count(DB::getQueryLog())];
DB::disableQueryLog();
echo json_encode($result,JSON_PRETTY_PRINT).PHP_EOL;
