<?php
namespace App\Services;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelSearchService
{
    public function query(array $data)
    {
        $nights = !empty($data['check_in']) ? (int) Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out'])) : 1;
        $boards = DB::table('room_board_types')->select('room_id')->selectRaw('MIN(price) as board_price')
            ->when($data['board_type'] ?? null, fn ($q, $id) => $q->where('board_type_id', $id))->groupBy('room_id');
        $rooms = DB::table('rooms as r')->joinSub($boards, 'b', 'b.room_id', '=', 'r.id')
            ->where('r.capacity', '>=', ($data['adults'] ?? 1) + ($data['children'] ?? 0));
        foreach ($data['room_facilities'] ?? [] as $id) {
            $rooms->whereExists(fn ($q) => $q->selectRaw('1')->from('facility_room as f')->whereColumn('f.room_id', 'r.id')->where('f.facility_id', $id));
        }
        $price = '(r.price_per_night + b.board_price)';
        if (!empty($data['check_in'])) {
            $inventory = DB::table('room_inventories')->select('room_id')
                ->where('date', '>=', $data['check_in'])->where('date', '<', $data['check_out'])
                ->selectRaw('COUNT(*) as days, COUNT(price) as priced_days, SUM(price) as stay_price, MIN(available) as available')->groupBy('room_id');
            $rooms->leftJoinSub($inventory, 'i', 'i.room_id', '=', 'r.id')
                ->where(fn ($q) => $q->whereNull('i.available')->orWhere('i.available', '>', 0))
                ->where(fn ($q) => $q->where('r.total_units', '>', 0)->orWhere('i.days', '=', $nights));
            // Missing inventory dates use room defaults, exactly as AvailabilityService.
            $price = "(COALESCE(i.stay_price, 0) + ($nights - COALESCE(i.priced_days, 0)) * r.price_per_night + $nights * b.board_price)";
        } else {
            $rooms->where('r.total_units', '>', 0);
        }
        $rooms->select('r.hotel_id')->selectRaw("$price as stay_price");
        $matches = DB::query()->fromSub($rooms, 'c')->select('hotel_id')->selectRaw('MIN(stay_price) as search_price')
            ->when(isset($data['min_price']), fn ($q) => $q->whereRaw('stay_price >= CAST(? AS DECIMAL(18,2))', [$data['min_price']]))
            ->when(isset($data['max_price']), fn ($q) => $q->whereRaw('stay_price <= CAST(? AS DECIMAL(18,2))', [$data['max_price']]))->groupBy('hotel_id');
        $hotels = Hotel::query()->where('published', true)
            ->when($data['city'] ?? null, function ($q, $city) use ($data) {
                // Canonical selections are exact; legacy free-text city URLs remain supported.
                return !empty($data['country']) ? $q->where('city', $city)->where('country', $data['country']) : $q->where('city', 'like', '%'.addcslashes($city, '%_\\').'%');
            })
            ->when($data['stars'] ?? [], fn ($q, $stars) => $q->whereIn('star_rating', $stars));
        foreach ($data['hotel_facilities'] ?? [] as $facility) $hotels->whereJsonContains('facilities', $facility);
        $requiresRoom = !empty($data['check_in']) || isset($data['adults']) || isset($data['children']) || !empty($data['room_facilities']) || !empty($data['board_type']) || isset($data['min_price']) || isset($data['max_price']) || in_array($data['sort'] ?? '', ['price_asc','price_desc']);
        $join = $requiresRoom ? 'joinSub' : 'leftJoinSub';
        $hotels->$join($matches, 'matches', 'matches.hotel_id', '=', 'hotels.id')
            ->select('hotels.*', 'matches.search_price')->with('featuredImage');
        match ($data['sort'] ?? 'recommended') {
            'price_asc' => $hotels->orderBy('search_price'),
            'price_desc' => $hotels->orderByDesc('search_price'),
            'stars' => $hotels->orderByDesc('star_rating'),
            default => $hotels->orderBy('name'),
        };
        return $hotels->orderBy('hotels.id');
    }
    public function options(): array {
        return [
            'hotelFacilities' => DB::table('hotels')->where('published', true)->whereNotNull('facilities')->distinct()->pluck('facilities')
                ->flatMap(fn ($json) => json_decode($json, true) ?? [])->unique()->sort()->values(),
            'roomFacilities' => DB::table('facilities')->orderBy('name')->get(['id','name']),
            'boards' => DB::table('board_types')->orderBy('name')->get(['id','name']),
        ];
    }
}
