<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;

class PublicHotelController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['city' => ['nullable', 'string', 'max:64']]);
        $hotels = Hotel::where('published', true)
            ->when($data['city'] ?? null, fn ($query, $city) => $query->where('city', 'like', '%'.$city.'%'))
            ->with('featuredImage')->withCount('rooms')->orderBy('name')->paginate(12)->withQueryString();

        return view('hotels.index', compact('hotels'));
    }

    public function show(Hotel $hotel)
    {
        abort_unless($hotel->published, 404);
        $hotel->load(['images', 'rooms.featuredImage', 'rooms.facilities', 'rooms.boardTypes']);

        return view('hotels.show', compact('hotel'));
    }
}
