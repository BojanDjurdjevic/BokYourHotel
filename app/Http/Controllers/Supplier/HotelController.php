<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
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
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        auth()->user()->hotels()->create($data);

        return redirect()
            ->route('supplier.hotels')
            ->with('success','Hotel created');
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
    public function edit(string $id)
    {
        //
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
