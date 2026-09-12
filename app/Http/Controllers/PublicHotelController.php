<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class PublicHotelController extends Controller
{
    public function index(\App\Http\Requests\HotelSearchRequest $request, \App\Services\HotelSearchService $search)
    {
        $data = $request->validated();
        $hotels = $search->query($data)->paginate(12)->withQueryString();

        return view('hotels.index', compact('hotels') + $search->options());
    }

    public function show(Hotel $hotel)
    {
        abort_unless($hotel->published, 404);
        $hotel->load(['images', 'rooms.featuredImage', 'rooms.facilities', 'rooms.boardTypes']);

        return view('hotels.show', compact('hotel'));
    }
}
