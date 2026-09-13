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
        abort_unless($hotel->published && ! $hotel->archived_at, 404);
        $hotel->load([
            'images' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            'rooms.featuredImage',
            'rooms.images' => fn ($query) => $query->orderBy('position')->orderBy('id'),
            'rooms.facilities',
            'rooms.boardTypes',
        ]);

        return view('hotels.show', compact('hotel'));
    }
}
