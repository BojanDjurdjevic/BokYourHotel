<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddHotelRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Hotel;

class HotelController extends Controller
{
    public function index()
    {
        //dd(auth()->user()->hotels());
        $hotels = auth()->user()
            ->hotels()
            ->latest()
            ->get();

        return view('supplier.hotels.index', compact('hotels'));
    }

    public function create()
    {
        return view('supplier.hotels.create');
    }
    
    public function store(AddHotelRequest $request)
    {
        $data = $request->validated();

        $name = $data['name'];

        auth()->user()->hotels()->create($data);

        return redirect()
            ->route('supplier.hotels.index')
            ->with('success',"New hotel $name successfully created!");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        return view('supplier.hotels.edit', compact('hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
